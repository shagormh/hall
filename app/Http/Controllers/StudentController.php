<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\CreateStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Requests\Student\UpdateStudentStatusRequest;
use App\Models\Student;
use App\Models\StudentBlockList;
use App\Models\HallAllotment;
use App\Models\Seat;
use App\Services\DepartmentService;
use App\Services\StudentService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StudentController extends Controller implements HasMiddleware
{
    protected StudentService $studentService;
    protected DepartmentService $departmentService;

    public function __construct(StudentService $studentService, DepartmentService $departmentService)
    {
        $this->studentService = $studentService;
        $this->departmentService = $departmentService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-create-student', only: ['create', 'store']),
            new Middleware('permission:can-edit-student', only: ['edit', 'update', 'updatePassword', 'changeStatus', 'unblock']),
            new Middleware('permission:can-delete-student', only: ['destroy']),
            new Middleware('permission:can-view-student', only: ['index']),
            new Middleware('permission:can-view-details-student', only: ['show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('studentList');
        $students = $this->studentService->getStudents();
        $departments = $this->departmentService->getDepartments();
        $responseData = [
            'students' => $students,
            'departments' => $departments,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Students',
        ];
        return inertia('Student/Index', $responseData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumbs = Breadcrumbs::generate('createStudent');
        $departments = $this->departmentService->getActiveDepartments();
        $responseData = [
            'departments' => $departments,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Create Student',
        ];
        return inertia('Student/Create', $responseData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateStudentRequest $request)
    {
        $validatedData = $request->validated();
        $student = $this->studentService->createStudent($validatedData);
        $status = $student ? 'success' : 'error';
        $message = $student ? 'Student created successfully.' : 'Failed to create student.';
        if ($request->routeIs('hallAttachments.student.store')) {
            return redirect()->route('hall-attachments.create', ['studentId' => $student->id])->with($status, $message);
        }
        return redirect()->route('students.index')->with($status, $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $breadcrumbs = Breadcrumbs::generate('editStudent', $student);
        $departments = $this->departmentService->getActiveDepartments();
        $responseData = [
            'student' => $student,
            'departments' => $departments,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Edit Student',
        ];
        return inertia('Student/Create', $responseData);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validatedData = $request->validated();
        $blockReason = $request->input('block_reason');

        try {
            // Check if is_active is being changed to false (blocking the student)
            if (isset($validatedData['is_active']) && $validatedData['is_active'] === false && $student->is_active === true) {
                // Block the student
                $student = $this->studentService->blockStudent($student, $blockReason);
            } else if (isset($validatedData['is_active']) && $validatedData['is_active'] === true && $student->is_active === false) {
                // Activate the student - clear block reason and update
                $validatedData['block_reason'] = null; // Clear block reason when activating
                $student = $this->studentService->updateStudent($student, $validatedData);
            } else {
                // Regular update (not changing active status)
                $student = $this->studentService->updateStudent($student, $validatedData);
            }

            $status = 'success';
            $message = 'Student updated successfully.';

            // If it's an AJAX request (from your Vue component), return JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'student' => $student->fresh()
                ]);
            }

            return redirect()->route('students.index')->with($status, $message);

        } catch (\Exception $e) {
            $status = 'error';
            $message = 'Failed to update student: ' . $e->getMessage();

            // If it's an AJAX request, return JSON error
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }

            return redirect()->back()->with($status, $message);
        }
    }
public function updateStatus(UpdateStudentStatusRequest $request, Student $student)
{
    try {
        $validatedData = $request->validated();

        if ($validatedData['is_active'] === false) {
            // BLOCK STUDENT - UPDATED FOR PENDING STATUS
            DB::transaction(function () use ($student, $validatedData) {
                Log::info("Starting block process for student: {$student->id}");

                // ১. Find allotment with ANY status (pending, active, approved etc.)
                $allotment = HallAllotment::where('student_id', $student->id)
                    ->whereIn('status', ['pending', 'active', 'approved', 'confirmed'])
                    ->first();

                Log::info("Allotment found: " . ($allotment ? "ID: {$allotment->id}, Status: {$allotment->status}" : 'None'));

                // ২. If allotment exists, cancel it
                if ($allotment) {
                    Log::info("Cancelling allotment: {$allotment->id}, Seat: {$allotment->seat_id}");

                    // SEAT FREE
                    $seatUpdate = Seat::where('id', $allotment->seat_id)
                        ->update([
                            'status' => 'empty'
                        ]);

                    Log::info("Seat update result: " . ($seatUpdate ? 'Success' : 'Failed'));

                    // ALLOTMENT CANCEL
                    $allotmentUpdate = $allotment->update([
                        // 'status' => 'cancelled',
                        'status' => 'blocked',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Student blocked: ' . ($validatedData['block_reason'] ?? 'No reason provided')
                    ]);

                    Log::info("Allotment update result: " . ($allotmentUpdate ? 'Success' : 'Failed'));
                } else {
                    Log::info("No allotment found to cancel for student: {$student->id}");
                }

                // ৩. STUDENT UPDATE
                $studentUpdate = $student->update([
                    'is_active' => false,
                    'hall_status' => 'attachment',
                    'block_reason' => $validatedData['block_reason'] ?? null
                ]);

                Log::info("Student update result: " . ($studentUpdate ? 'Success' : 'Failed'));

                // ৪. BLOCK LIST ENTRY
                StudentBlockList::create([
                    'student_id' => $student->id,
                    'blocked_by' => Auth::id(),
                    'reason' => $validatedData['block_reason'] ?? null,
                    'blocked_at' => now(),
                ]);

                Log::info("Block process completed for student: {$student->id}");
            });

            $message = 'Student blocked and allotment cancelled successfully.';

        } else {
            // ACTIVATE STUDENT
            $student->update([
                'is_active' => true,
                'block_reason' => null
            ]);
            $message = 'Student activated successfully.';
        }

        return redirect()->back()->with('success', $message);

    } catch (\Exception $e) {
        Log::error("Block student error: " . $e->getMessage());
        return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
    }
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $isDeleted = $this->studentService->deleteStudent($student);
        $status = $isDeleted ? 'success' : 'error';
        $message = $isDeleted ? 'Student deleted successfully.' : 'Failed to delete student.';
        return redirect()->route('students.index')->with($status, $message);
    }

    /**
     * Display blocked students list.
     */
    public function blockList()
    {
        $breadcrumbs = Breadcrumbs::generate('studentBlockList');
        $students = $this->studentService->getBlockedStudents();
        $departments = $this->departmentService->getDepartments();
        $responseData = [
            'students' => $students,
            'departments' => $departments,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Blocked Students',
        ];
        return inertia('Student/BlockList', $responseData);
    }

    /**
     * Unblock a student.
     */
    public function unblock(Student $student)
    {
        $student->is_active = true;
        $student->save();

        // Update the block list record
        StudentBlockList::where('student_id', $student->id)->update(['unblocked_at' => now()]);

        return redirect()->back()->with('success', 'Student unblocked successfully.');
    }
}

