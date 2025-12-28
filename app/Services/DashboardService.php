<?php

namespace App\Services;

use App\Models\Hall;
use App\Models\HallAllotment;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Student;
use App\Models\StudentBlockList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardService
{
    /**
     * Get statistics for dashboard cards
     */
    public function getStatistics()
    {
        $user = Auth::user();
        // halls is already an array of IDs, not a relationship
        $hallIds = collect($user->halls ?? []);
        
        // For admin: all data, For provost: only their hall data
        $isAdmin = $user->hasRole('super-admin');
        
        return [
            'students' => $this->getStudentStatistics($hallIds, $isAdmin),
            'seats' => $this->getSeatStatistics($hallIds, $isAdmin),
            'halls' => $this->getHallStatistics($isAdmin),
            'rooms' => $this->getRoomStatistics($hallIds, $isAdmin),
        ];
    }

    /**
     * Get student related statistics
     */
    private function getStudentStatistics($hallIds, $isAdmin)
    {
        $query = Student::query();
        
        if (!$isAdmin) {
            $query->whereIn('hall_id', $hallIds);
        }
        
        $total = $query->count();
        $allotted = (clone $query)->where('hall_status', 'alloted')->count();
        $attachment = (clone $query)->where('hall_status', 'attachment')->count();
        $blocked = (clone $query)->where('is_active', false)->count();
        
        return [
            'total' => $total,
            'allotted' => $allotted,
            'attachment' => $attachment,
            'blocked' => $blocked,
        ];
    }

    /**
     * Get seat related statistics
     */
    private function getSeatStatistics($hallIds, $isAdmin)
    {
        $query = Seat::query();
        
        if (!$isAdmin) {
            // Seats are related to rooms, rooms have hall_id
            $query->whereHas('room', function($q) use ($hallIds) {
                $q->whereIn('hall_id', $hallIds);
            });
        }
        
        $total = $query->count();
        $allotted = (clone $query)->where('status', 'alloted')->count();
        $empty = (clone $query)->where('status', 'empty')->count();
        
        $occupancyRate = $total > 0 ? round(($allotted / $total) * 100, 1) : 0;
        
        return [
            'total' => $total,
            'allotted' => $allotted,
            'empty' => $empty,
            'occupancy_rate' => $occupancyRate,
        ];
    }

    /**
     * Get hall statistics (admin only sees all)
     */
    private function getHallStatistics($isAdmin)
    {
        if (!$isAdmin) {
            return null; // Provost doesn't need this
        }
        
        return [
            'total' => Hall::count(),
            'active' => Hall::where('is_active', true)->count(),
        ];
    }

    /**
     * Get room statistics
     */
    private function getRoomStatistics($hallIds, $isAdmin)
    {
        $query = Room::query();
        
        if (!$isAdmin) {
            $query->whereIn('hall_id', $hallIds);
        }
        
        return [
            'total' => $query->count(),
        ];
    }

    /**
     * Get pending actions that need attention
     */
    public function getPendingActions()
    {
        $user = Auth::user();
        $hallIds = collect($user->halls ?? []);
        
        $cancellationRequests = HallAllotment::whereIn('hall_id', $hallIds)
            ->where('status', 'cancel_requested')
            ->count();
        
        $attachmentStudents = Student::whereIn('hall_id', $hallIds)
            ->where('hall_status', 'attachment')
            ->where('is_active', true)
            ->count();
        
        $blockedStudents = StudentBlockList::whereHas('student', function ($query) use ($hallIds) {
            $query->whereIn('hall_id', $hallIds);
        })->count();
        
        return [
            'cancellation_requests' => $cancellationRequests,
            'attachment_students' => $attachmentStudents,
            'blocked_students' => $blockedStudents,
        ];
    }

    /**
     * Get hall-wise occupancy data for chart
     * Fixed: Eliminated N+1 query by using single query with joins
     */
    public function getHallOccupancyData()
    {
        $user = Auth::user();
        $hallIds = collect($user->halls ?? []);
        
        // Single optimized query with joins
        $occupancyData = DB::table('halls')
            ->leftJoin('rooms', 'halls.id', '=', 'rooms.hall_id')
            ->leftJoin('seats', 'rooms.id', '=', 'seats.room_id')
            ->whereIn('halls.id', $hallIds)
            ->select(
                'halls.id',
                'halls.name as hall_name',
                DB::raw('COUNT(seats.id) as total_seats'),
                DB::raw('SUM(CASE WHEN seats.status = "alloted" THEN 1 ELSE 0 END) as allotted_seats')
            )
            ->groupBy('halls.id', 'halls.name')
            ->get();
        
        $data = [];
        
        foreach ($occupancyData as $hall) {
            $totalSeats = $hall->total_seats ?? 0;
            $allottedSeats = $hall->allotted_seats ?? 0;
            $occupancyRate = $totalSeats > 0 ? round(($allottedSeats / $totalSeats) * 100, 1) : 0;
            
            $data[] = [
                'hall_name' => $hall->hall_name,
                'total_seats' => $totalSeats,
                'allotted_seats' => $allottedSeats,
                'empty_seats' => $totalSeats - $allottedSeats,
                'occupancy_rate' => $occupancyRate,
            ];
        }
        
        return $data;
    }

    /**
     * Get monthly allotment trends for the last 6 months
     */
    public function getMonthlyTrendsData()
    {
        $user = Auth::user();
        $hallIds = collect($user->halls ?? []);
        
        $trends = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthName = now()->subMonths($i)->format('M Y');
            
            $count = HallAllotment::whereIn('hall_id', $hallIds)
                ->where('status', 'active')
                ->whereYear('allotment_date', '=', now()->subMonths($i)->year)
                ->whereMonth('allotment_date', '=', now()->subMonths($i)->month)
                ->count();
            
            $trends[] = [
                'month' => $monthName,
                'count' => $count,
            ];
        }
        
        return $trends;
    }

    /**
     * Get recent activities from activity log
     */
    public function getRecentActivities($limit = 10)
    {
        try {
            $user = Auth::user();
            $hallIds = collect($user->halls ?? []);
            
            // Get recent activities related to the halls
            $activities = Activity::with('causer')
                ->whereIn('subject_type', [
                    'App\Models\Student',
                    'App\Models\HallAllotment',
                    'App\Models\Seat',
                ])
                ->latest()
                ->take($limit)
                ->get()
                ->map(function ($activity) {
                    return [
                        'description' => $activity->description,
                        'subject_type' => class_basename($activity->subject_type),
                        'causer_name' => $activity->causer ? $activity->causer->name : 'System',
                        'created_at' => $activity->created_at->diffForHumans(),
                        'created_at_formatted' => $activity->created_at->format('M d, Y h:i A'),
                    ];
                });
            
            return $activities;
        } catch (\Exception $e) {
            // If activity_log table doesn't exist or query fails, log and return empty collection
            \Log::error('Failed to load recent activities: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get all dashboard data in one call
     */
    public function getDashboardData()
    {
        return [
            'statistics' => $this->getStatistics(),
            'pending_actions' => $this->getPendingActions(),
            'hall_occupancy' => $this->getHallOccupancyData(),
            'monthly_trends' => $this->getMonthlyTrendsData(),
            'recent_activities' => $this->getRecentActivities(),
        ];
    }
}
