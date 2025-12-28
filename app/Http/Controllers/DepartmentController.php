<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\CreateDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class DepartmentController extends Controller implements HasMiddleware
{
    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-create-department', only: ['create', 'store']),
            new Middleware('permission:can-edit-department', only: ['edit', 'update', 'changeStatus']),
            new Middleware('permission:can-delete-department', only: ['destroy']),
            new Middleware('permission:can-view-department', only: ['index']),
            new Middleware('permission:can-view-details-department', only: ['show']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('departments');
        $departments = $this->departmentService->getDepartments();
        $responseData = [
            'breadcrumbs' => $breadcrumbs,
            'departments' => $departments,
            'pageTitle' => 'Departments List',
        ];
        return Inertia::render('Department/Index', $responseData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumbs = Breadcrumbs::generate('createDepartment');
        $responseData = [
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Create Department',
        ];
        return Inertia::render('Department/Create', $responseData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateDepartmentRequest $request)
    {
        $validatedData = $request->validated();
        $department = $this->departmentService->create($validatedData);
        $status = $department ? 'success' : 'error';
        $message = $department ? 'Department created successfully.' : 'Failed to create department.';
        return redirect()->route('departments.index')->with($status, $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $breadcrumbs = Breadcrumbs::generate('editDepartment', $department);
        $responseData = [
            'breadcrumbs' => $breadcrumbs,
            'department' => $department,
            'pageTitle' => 'Edit Department',
        ];
        return Inertia::render('Department/Create', $responseData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $validatedData = $request->validated();
        $updatedDepartment = $this->departmentService->update($department, $validatedData);
        $status = $updatedDepartment ? 'success' : 'error';
        $message = $updatedDepartment ? 'Department updated successfully.' : 'Failed to update department.';
        return redirect()->route('departments.index')->with($status, $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $deleted = $department->delete();
        $status = $deleted ? 'success' : 'error';
        $message = $deleted ? 'Department deleted successfully.' : 'Failed to delete department.';
        return redirect()->route('departments.index')->with($status, $message);
    }

    public function changeStatus(Request $request, Department $department)
    {
        $department = $this->departmentService->changeStatus($department, $request->is_active);
        $status = $department ? "success" : "error";
        $message = $department->is_active ? 'Department is active' : 'Department is deactive';
        return redirect()->back()->with($status, $message);
    }
}
