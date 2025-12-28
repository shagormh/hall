<?php

namespace App\Services;

use App\Constants\Constants;
use App\Models\HallAllotment;
use App\Models\Seat;
use App\Models\Student;
use App\Models\StudentBlockList;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentService extends BaseModelService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function model(): string
    {
        return Student::class;
    }

    public function getStudents()
    {
        return $this->model()::whereHas('department', function($query) {
                $query->where('is_active', true);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function getStudentsWithOutAttachOrAllotment()
    {
        return $this->model()::where('hall_status', null)
            ->whereHas('department', function($query) {
                $query->where('is_active', true);
            })
            ->get();
    }

    public function getAttachmentStudents()
    {
        $user = Auth::user();
        $hallIds = $user->halls()->pluck('id');

        return $this->model()::whereIn('hall_id', $hallIds)
            ->where('hall_status', 'attachment')
            ->where('is_active', true)
            ->whereHas('department', function($query) {
                $query->where('is_active', true);
            })
            ->get();
    }

  // Get all students with allotment status (ONLY for provost's hall)
    // Fixed: Removed N+1 query by using eager loading
    // Only students from active departments
    public function getAllStudents()
    {
        $user = Auth::user();
        $hallIds = $user->halls;

        return $this->model()::whereIn('hall_id', $hallIds)
            ->where('is_active', true)
            ->whereIn('hall_status', ['attachment', 'cancel'])
            ->whereHas('department', function($query) {
                $query->where('is_active', true);
            })
            ->with(['hallAllotments' => function($query) {
                $query->whereIn('status', ['active', 'cancel_requested', 'cancelled'])
                      ->latest();
            }])
            ->orderBy('roll', 'asc')
            ->get()
            ->map(function ($student) {
                $student->allotment_status = $this->computeAllotmentStatus($student->hallAllotments);
                return $student;
            });
    }

    // Get students for re-allotment (ONLY for provost's hall)
    // Only students from active departments
    public function getStudentsForReallotment()
    {
        $user = Auth::user();
        $hallIds = $user->halls;

        // Get students who have cancelled allotments (only from provost's hall)
        $cancelledStudentIds = HallAllotment::where('status', 'cancelled')
            ->whereHas('student', function ($query) use ($hallIds) {
                $query->whereIn('hall_id', $hallIds);
            })
            ->pluck('student_id')
            ->unique();

        return $this->model()::whereIn('id', $cancelledStudentIds)
            ->whereIn('hall_id', $hallIds)
            ->whereHas('department', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('roll', 'asc')
            ->get();
    }

    // Compute student's current allotment status from loaded relationships
    private function computeAllotmentStatus($hallAllotments)
    {
        if ($hallAllotments->isEmpty()) {
            return 'never_alloted';
        }

        // Check for active or cancel_requested allotments
        $currentAllotment = $hallAllotments->whereIn('status', ['active', 'cancel_requested'])->first();
        
        if ($currentAllotment) {
            return $currentAllotment->status === 'active' ? 'currently_alloted' : 'cancel_requested';
        }

        // Check for cancelled allotments
        $cancelledAllotment = $hallAllotments->where('status', 'cancelled')->first();
        
        return $cancelledAllotment ? 'previously_cancelled' : 'never_alloted';
    }

    public function createStudent(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {
            $email = $validatedData['email'] ?? $validatedData['roll'] . '@gmail.com';
            $user = User::firstOrCreate(
                ['email' => $email], // search condition
                [
                    'name' => $validatedData['name'],
                    'password' => bcrypt('12345'),
                ]
            );
            $user->assignRole(Constants::ROLE_STUDENT);
            $validatedData['user_id'] = $user->id;
            $student = $this->create($validatedData);
            return $student;
        });
    }

    public function updateStudent(Student $student, $validatedData)
    {
        $result = DB::transaction(function () use($student, $validatedData) {
            $this->update($student, $validatedData);

            // Update user name
            if(isset($validatedData['name'])) {
                $user = $student->user;
                if($user) {
                    $user->name = $validatedData['name'];
                    $user->save();
                }
            }
            return $student;
        });
        return $result;
    }

    public function deleteStudent(Student $student)
    {
        return DB::transaction(function () use ($student) {
            $user = $student->user;
            if ($user) {
                $user->delete();
            }
            return $student->delete();
        });
    }

    public function getBlockedStudents()
    {
        return $this->model()::where('is_active', false)
            ->whereHas('department', function($query) {
                $query->where('is_active', true);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function blockStudent(Student $student, $reason = null)
    {
        return DB::transaction(function () use ($student, $reason) {
            // Manual way - DI ছাড়াই
            $activeAllotment = HallAllotment::where('student_id', $student->id)
                ->where('status', 'active')
                ->first();

            if ($activeAllotment) {
                // Manual seat update
                DB::table('seats')
                    ->where('id', $activeAllotment->seat_id)
                    ->update(['status' => 'empty']);

                // Manual allotment update
                DB::table('hall_allotments')
                    ->where('id', $activeAllotment->id)
                    ->update([
                        // 'status' => 'cancelled',
                        'status' => 'blocked',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Student blocked: ' . ($reason ?? 'No reason provided')
                    ]);
            }

            // Student update
            $student->update([
                'hall_status' => 'attachment',
                'is_active' => false
            ]);

            // Block list
            StudentBlockList::create([
                'student_id' => $student->id,
                'blocked_by' => Auth::id(),
                'reason' => $reason,
                'blocked_at' => now(),
            ]);

            return $student;

        });
    }


    public function unblockStudent(Student $student)
    {
        return DB::transaction(function () use ($student) {
            // Student update
            $student->update([
                'is_active' => true

            ]);

            // Remove from block list
            StudentBlockList::where('student_id', $student->id)->delete();

            return $student;
        });
    }

}


