<?php

namespace App\Http\Controllers;

use App\Models\StudentBlockList;
use App\Models\Student;
use App\Services\StudentBlockListService;
use App\Services\StudentService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StudentBlockListController extends Controller
{
    protected StudentBlockListService $blockListService;
    protected StudentService $studentService;



    public function __construct(StudentBlockListService $blockListService, StudentService $studentService)
    {
        $this->blockListService = $blockListService;
        $this->studentService = $studentService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-view-block-student', only: ['index']),
            new Middleware('permission:can-delete-block-student', only: ['destroy']),
            new Middleware('permission:can-unblock-student', only: ['unblock']),
        ];
    }

    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('studentBlockList');

        $blocks = StudentBlockList::whereNull('deleted_at')
                    ->with(['student', 'blockedBy'])
                    ->orderByDesc('blocked_at')
                    ->get();

        $departments = app(\App\Services\DepartmentService::class)->getDepartments();

        return inertia('Student/BlockList', [
            'students' => $blocks->map(fn($b) => [
                'block_id' => $b->id,
                'id' => $b->student->id,
                'roll' => $b->student->roll,
                'registration' => $b->student->registration,
                'name' => $b->student->name,
                'department' => $b->student->department_id ?? 'N/A', // department name show করুন
                'mobile_number' => $b->student->mobile_number, // শুধু mobile number
                'is_active' => $b->student->is_active,
                'blocked_at' => $b->blocked_at->format('d M Y h:i A'), // formatted date
                'unblocked_at' => $b->unblocked_at ? $b->unblocked_at->format('d M Y h:i A') : null,
                'reason' => $b->reason,
                'blocked_by' => $b->blockedBy->name ?? 'N/A', // blocked by user name
            ]),
            'departments' => $departments,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Blocked Students',
        ]);
    }



    public function show($id)
    {
        $entry = $this->blockListService->findById($id);
        return inertia('Student/BlockList', ['entry' => $entry]);
    }

    public function destroy($id)
    {
        $this->blockListService->deleteEntry($id);
        return redirect()->back()->with('success', 'Block entry removed.');
    }

    public function unblock($id)
    {
        try {
            $entry = StudentBlockList::findOrFail($id);
            $student = $entry->student;

            // Step 1: Update student status
            $student->update([
                'is_active' => true,
                'block_reason' => null
            ]);

            // Step 2: Update block entry with unblock timestamp (DELETE করবেন না)
            $entry->update([
                'unblocked_at' => now()
            ]);

            return redirect()->back()->with('success', 'Student unblocked successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to unblock student: ' . $e->getMessage());
        }
    }
}
