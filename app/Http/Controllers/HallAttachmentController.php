<?php

namespace App\Http\Controllers;

use App\Http\Requests\HallAttachment\CreateHallAttachmentRequest;
use App\Http\Requests\HallAttachment\UpdateHallAttachmentRequest;
use App\Models\HallAttachment;
use App\Services\DepartmentService;
use App\Services\HallAttachmentService;
use App\Services\HallService;
use App\Services\StudentService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HallAttachmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-view-hall-attachment', only: ['index']),
            new Middleware('permission:can-create-hall-attachment', only: ['create', 'store']),
            new Middleware('permission:can-edit-hall-attachment', only: ['edit', 'update']),
            new Middleware('permission:can-delete-hall-attachment', only: ['destroy']),
            new Middleware('permission:can-change-status-hall-attachment', only: ['changeStatus']),
        ];
    }
    protected HallAttachmentService $hallAttachmentService;
    protected StudentService $studentService;
    protected HallService $hallService;
    protected DepartmentService $departmentService;

    public function __construct(HallAttachmentService $hallAttachmentService, StudentService $studentService, HallService $hallService, DepartmentService $departmentService)
    {
        $this->hallAttachmentService = $hallAttachmentService;
        $this->studentService = $studentService;
        $this->hallService = $hallService;
        $this->departmentService = $departmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('hallAttachments');
        $hallAttachments = $this->hallAttachmentService->getHallAttachmentByProvost();
        $responseData = [
            'hallAttachments' => $hallAttachments,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Hall Attachment List',
        ];

        return Inertia::render('HallAttachment/Index', $responseData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('createHallAttachment');
        $studentId = $request->query('studentId');
        $students = $this->studentService->getStudentsWithOutAttachOrAllotment();
        $halls = $this->hallService->getHalls();
        $departments = $this->departmentService->getActiveDepartments();
        $responseData = [
            'students' => $students,
            'halls' => $halls,
            'departments' => $departments,
            'studentId' => $studentId,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Add Hall Attachment',
        ];
        return Inertia::render('HallAttachment/Create', $responseData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateHallAttachmentRequest $request)
    {
        $validatedData = $request->validated();
        $hallAttachment = $this->hallAttachmentService->createHallAttachment($validatedData);
        $status = $hallAttachment ? 'success' : 'error';
        $message = $hallAttachment ? 'Hall attachment created successfully.' : 'Failed to create hall attachment.';
        return redirect()->route('hall-attachments.index')->with($status, $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(HallAttachment $hallAttachment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HallAttachment $hallAttachment)
    {
        $breadcrumbs = Breadcrumbs::generate('editHallAttachment', $hallAttachment);
        $students = $this->studentService->getStudents();
        $halls = $this->hallService->getHalls();
        $responseData = [
            'hallAttachment' => $hallAttachment,
            'students' => $students,
            'halls' => $halls,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Add Hall Attachment',
        ];
        return Inertia::render('HallAttachment/Create', $responseData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHallAttachmentRequest $request, HallAttachment $hallAttachment)
    {
        $validatedData = $request->validated();
        $isUpdated = $this->hallAttachmentService->updateHallAttachment($hallAttachment, $validatedData);
        $status = $isUpdated ? 'success' : 'error';
        $message = $isUpdated ? 'Hall attachment updated successfully.' : 'Failed to update hall attachment.';
        return redirect()->route('hall-attachments.index')->with($status, $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HallAttachment $hallAttachment)
    {
        $deleteAttachment = $this->hallAttachmentService->deleteHallAttachment($hallAttachment);
        $status = $deleteAttachment ? 'success' : 'error';
        $message = $deleteAttachment ? 'Hall attachment deleted successfully.' : 'Failed to delete hall attachment.';
        return redirect()->route('hall-attachments.index')->with($status, $message);
    }

    public function changeStatus(Request $request, HallAttachment $hallAttachment)
    {
        $department = $this->hallAttachmentService->changeStatus($hallAttachment, $request->is_active);
        $status = $department ? "success" : "error";
        $message = $department->is_active ? 'Hall attachment is active' : 'Hall attachment is deactivate';
        return redirect()->back()->with($status, $message);
    }
}
