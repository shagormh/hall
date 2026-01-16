<?php

namespace App\Services;

use App\Models\HallAllotment;
use App\Models\Seat;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HallAllotmentService extends BaseModelService
{
    public function model(): string
    {
        return HallAllotment::class;
    }

    public function getHallAllotments()
    {
        return $this->model()::with(['student', 'hall', 'seat'])->get();
    }

    public function getHallAllotmentByProvost()
    {
        $user = Auth::user();
        $hallIds = $user->halls;

        return $this->model()::with(['student', 'hall', 'seat'])
            ->whereIn('hall_id', $hallIds)
            ->get();
    }

    public function createHallAllotment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $startingMonth = $data['starting_month'];

            // Check if seat is available for the selected starting month
            $this->validateSeatAvailability($data['seat_id'], $startingMonth);
            
            // ✅ Check student eligibility (no same-month re-allotment)
            $this->validateStudentOneAllotmentPerMonth($data['student_id'], $startingMonth);

            // ✅ Force status to active
            $data['status'] = 'active';

            $hallAllotment = $this->create($data);

            $student = Student::find($data['student_id']);
            $student->update([
                'hall_id' => $hallAllotment->hall_id,
                'hall_status' => 'alloted'
            ]);

            $seat = Seat::find($data['seat_id']);
            $seat->update([
                'status' => 'alloted'
            ]);

            return $hallAllotment;
        });
    }

    public function updateHallAllotment($hallAllotment, array $data)
    {
        return DB::transaction(function () use ($hallAllotment, $data) {
            if ($data['seat_id'] != $hallAllotment->seat_id) {
                $this->validateSeatAvailability($data['seat_id'], $data['starting_month']);
                // Also validate student if moving to a new seat (though strictly they are just moving)
                // But if they are just editing, maybe strict month rule applies? 
                // Let's assume on Edit, we mainly care about Seat Availability.
                // If they change starting month on edit, let's enforce student rule too just in case.
                 $this->validateStudentOneAllotmentPerMonth($data['student_id'], $data['starting_month']);
            } elseif ($data['starting_month'] != $hallAllotment->starting_month) {
                 // Even if seat matches, if starting month changes, check logic
                 $this->validateStudentOneAllotmentPerMonth($data['student_id'], $data['starting_month']);
            }

            $oldSeatId = $hallAllotment->seat_id;
            $hallAllotment->update($data);

            $student = Student::find($data['student_id']);
            $student->update([
                'hall_id' => $hallAllotment->hall_id
            ]);

            if ($oldSeatId && $oldSeatId != $hallAllotment->seat_id) {
                Seat::find($oldSeatId)?->update(['status' => 'empty']);
            }

            $newSeat = Seat::find($hallAllotment->seat_id);
            $newSeat->update(['status' => 'alloted']);

            return $hallAllotment;
        });
    }

    public function deleteHallAllotment($hallAllotment)
    {
        return DB::transaction(function () use ($hallAllotment) {
            // Reset student's hall_id
            $student = Student::find($hallAllotment->student_id);
            if ($student) {
                $student->update([
                    'hall_id' => null,
                    'hall_status' => 'cancel',
                ]);
            }

            // Free the seat
            $seat = Seat::find($hallAllotment->seat_id);
            if ($seat) {
                $seat->update([
                    'status' => 'empty'
                ]);
            }

            // Delete hall allotment
            $hallAllotment->delete();

            return true;
        });
    }

    // ✅ Request cancellation (not immediate cancel)
    public function requestCancel($hallAllotmentId, $endingMonth)
    {
        return DB::transaction(function () use ($hallAllotmentId, $endingMonth) {
            $hallAllotment = HallAllotment::findOrFail($hallAllotmentId);

            // ✅ Validate: Ending month should be after starting month
            $startingMonth = Carbon::parse($hallAllotment->starting_month);
            $endingMonthDate = Carbon::parse($endingMonth);

            if ($endingMonthDate->lte($startingMonth)) {
                throw new \Exception('Cancellation month must be after the starting month.');
            }

            $hallAllotment->update([
                'status' => 'cancel_requested',
                'ending_month' => $endingMonth, // ✅ Directly set ending_month
                'cancel_request_date' => Carbon::now()
            ]);

            $student = Student::find($hallAllotment->student_id);
            if ($student) {
                $student->update(['hall_status' => 'cancel']);
            }
            $seat = Seat::find($hallAllotment->seat_id);
            if ($seat) {
                $seat->update(['status' => 'empty']);
            }

            return $hallAllotment;
        });
    }

    // ✅ Approve cancellation (Admin action)
    public function approveCancel($hallAllotmentId)
    {
        return DB::transaction(function () use ($hallAllotmentId) {
            $hallAllotment = HallAllotment::findOrFail($hallAllotmentId);

            // ✅ Just change status to cancelled (ending_month already set)
            $hallAllotment->update([
                'status' => 'cancelled'
            ]);

            // Free the seat for new allotments
            $seat = Seat::find($hallAllotment->seat_id);
            if ($seat) {
                $seat->update(['status' => 'empty']);
            }

            // Update student status
            $student = Student::find($hallAllotment->student_id);
            if ($student) {
                $student->update(['hall_status' => 'cancel']);
            }

            return $hallAllotment;
        });
    }

    public function cancelHallAllotment($hallAllotmentId, $endingMonth = null)
    {
        return DB::transaction(function () use ($hallAllotmentId, $endingMonth) {
            $hallAllotment = HallAllotment::findOrFail($hallAllotmentId);

            // ✅ Use provided ending month or calculate current month's end
            if ($endingMonth) {
                $endingMonth = Carbon::parse($endingMonth);
            } else {
                $endingMonth = Carbon::now()->endOfMonth();
            }

            $hallAllotment->update([
                'status' => 'cancelled',
                'ending_month' => $endingMonth,
                'cancel_request_date' => Carbon::now()
            ]);

            // Free the seat for new allotments
            $seat = Seat::find($hallAllotment->seat_id);
            if ($seat) {
                $seat->update(['status' => 'empty']);
            }

            // Update student status
            $student = Student::find($hallAllotment->student_id);
            if ($student) {
                $student->update(['hall_status' => 'cancel']);
            }

            return $hallAllotment;
        });
    }

    public function validateSeatAvailability($seatId, $startingMonth)
    {
        $conflictingAllotment = HallAllotment::where('seat_id', $seatId)
            ->where('status', 'active')
            ->where('starting_month', '<=', $startingMonth)
            ->where(function($query) use ($startingMonth) {
                $query->where('ending_month', '>=', $startingMonth)
                      ->orWhereNull('ending_month');
            })
            ->exists();

        if ($conflictingAllotment) {
            throw new \Exception('Seat is not available for the selected starting month.');
        }

        // ✅ Check cooldown: If seat was cancelled in this month, it cannot be re-taken
        $this->validateSeatCooldown($seatId, $startingMonth);
    }
    
    /**
     * Ensure student doesn't get a new seat in the same month they cancelled one.
     */
    public function validateStudentOneAllotmentPerMonth($studentId, $startingMonth)
    {
        $conflict = HallAllotment::where('student_id', $studentId)
            ->where(function ($query) use ($startingMonth) {
                // Case 1: Has explicit ending month (cancelled/fixed term) and overlaps with selected month
                $query->where('ending_month', '>=', $startingMonth)
                      // Case 2: No ending month (permanent/active) and started before selected month
                      ->orWhere(function($q) use ($startingMonth) {
                          $q->whereNull('ending_month')
                            ->where('starting_month', '<=', $startingMonth)
                            // Only "living" statuses block forever.
                            // 'blocked' or 'cancelled' records with NULL end (if any) shouldn't block future.
                            ->whereIn('status', \App\Constants\AllotmentStatus::active());
                      });
            })
            ->exists();

        if ($conflict) {
            // "This student has a seat cancelled/active this month. Cannot allot new seat this month."
            throw new \Exception('এই ছাত্রের এই মাসে সিট বাতিল করা হয়েছে। বা সিট বরাদ্দ আছে, এই মাসে নতুন সিট বরাদ্দ দেওয়া যাবে না।');
        }
    }

    /**
     * Ensure seat isn't re-allotted in the same month it was cancelled.
     */
    public function validateSeatCooldown($seatId, $startingMonth)
    {
        $seatConflict = HallAllotment::where('seat_id', $seatId)
            ->where('ending_month', '>=', $startingMonth)
            ->exists();

        if ($seatConflict) {
            // "Seat was cancelled this month. Allotment available from next month."
            throw new \Exception('seat টি এই মাসে ক্যান্সেল করা হয়েছে পরের মাস থেকে এই সিটে allotment দেওয়া যাবে।');
        }
    }

    public function getAvailableSeats($hallId, $forMonth = null)
    {
        if (!$forMonth) {
            $forMonth = Carbon::now()->addMonth()->firstOfMonth();
        }

        $occupiedSeatIds = HallAllotment::where('hall_id', $hallId)
            ->where(function($q) {
                $q->where('status', 'active')
                  ->orWhere('status', 'cancelled')
                  ->orWhere('status', 'cancel_requested');
            })
            ->where('starting_month', '<=', $forMonth)
            ->where(function($query) use ($forMonth) {
                $query->where('ending_month', '>=', $forMonth)
                      ->orWhereNull('ending_month');
            })
            ->pluck('seat_id');

        // Fixed: Include rooms with NULL room_type OR active room types
        return Seat::whereHas('room', function($query) use ($hallId) {
                $query->where('hall_id', $hallId)
                      ->where(function($q) {
                          // Allow rooms with NULL room_type OR active room types from SAME hall
                          $q->whereNull('room_type_id')
                            ->orWhereHas('roomType', function($q2) {
                                $q2->where('is_active', true)
                                   ->whereColumn('room_types.hall_id', 'rooms.hall_id');
                            });
                      });
            })
            ->whereNotIn('id', $occupiedSeatIds)
            ->where('status', 'empty')
            ->with(['room.roomType'])
            ->get();
    }

    public function getStudentAllotmentHistory($studentId)
    {
        return $this->model()::with(['hall', 'seat'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get months for dropdown
     */
    public function getCurrentYearMonths()
    {
        $currentDate = now();
        $startMonth = $currentDate->copy()->firstOfMonth();
        $endMonth = $currentDate->copy()->addMonths(12)->firstOfMonth(); // Next 1 year

        $months = [];

        while ($startMonth->lte($endMonth)) {
            $months[] = [
                'name' => $startMonth->format('M - Y'),
                'value' => $startMonth->format('Y-m-d'),
            ];
            $startMonth->addMonth();
        }

        return $months;
    }

    /**
     * Get next available month
     */
    public function getNextAvailableMonth()
    {
        return Carbon::now()->addMonth()->firstOfMonth()->format('Y-m-d');
    }

    // ✅ NEW: Get pending cancellation requests (for admin)
    public function getPendingCancellations()
    {
        $user = Auth::user();
        $hallIds = $user->halls;

        return $this->model()::with(['student', 'hall', 'seat'])
            ->whereIn('hall_id', $hallIds)
            ->where('status', 'cancel_requested')
            ->get();
    }
}
