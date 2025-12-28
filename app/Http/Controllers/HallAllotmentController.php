<?php

namespace App\Http\Controllers;

use App\Http\Requests\HallAllotment\CreateHallAllotmentRequest;
use App\Http\Requests\HallAllotment\UpdateHallAllotmentRequest;
use App\Models\HallAllotment;
use App\Services\DepartmentService;
use App\Services\HallAllotmentService;
use App\Services\HallService;
use App\Services\RoomService;
use App\Services\SeatService;
use App\Services\StudentService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HallAllotmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-view-hall-allotment', only: ['index']),
            new Middleware('permission:can-create-hall-allotment', only: ['create', 'store']),
            new Middleware('permission:can-edit-hall-allotment', only: ['edit', 'update']),
            new Middleware('permission:can-delete-hall-allotment', only: ['destroy']),
            new Middleware('permission:can-cancel-hall-allotment', only: ['cancel']),
            new Middleware('permission:can-request-cancel-hall-allotment', only: ['requestCancel']),
            new Middleware('permission:can-approve-cancel-hall-allotment', only: ['approveCancel']),
        ];
    }
    protected HallAllotmentService $hallAllotmentService;
    protected StudentService $studentService;
    protected HallService $hallService;
    protected DepartmentService $departmentService;
    protected SeatService $seatService;
    protected RoomService $roomService;

    public function __construct(
        HallAllotmentService $hallAllotmentService,
        StudentService $studentService,
        HallService $hallService,
        DepartmentService $departmentService,
        SeatService $seatService,
        RoomService $roomService
    ) {
        $this->hallAllotmentService = $hallAllotmentService;
        $this->studentService = $studentService;
        $this->hallService = $hallService;
        $this->departmentService = $departmentService;
        $this->seatService = $seatService;
        $this->roomService = $roomService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('hallAllotments');
        $hallAllotments = $this->hallAllotmentService->getHallAllotmentByProvost();

        return Inertia::render('HallAllotment/Index', [
            'hallAllotments' => $hallAllotments,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Hall Allotment List',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('createHallAllotment');
        $studentId = $request->query('studentId');

        // ✅ Use the new method to get all students with status
        $students = $this->studentService->getAllStudents();

        $halls = $this->hallService->getHalls();
        $departments = $this->departmentService->getActiveDepartments();
        $seats = $this->seatService->getEmptySeatByHallProvost();
        $rooms = $this->roomService->getRoomByProvost();
        $getMonths = $this->hallAllotmentService->getCurrentYearMonths();

        return Inertia::render('HallAllotment/Create', [
            'students' => $students,
            'halls' => $halls,
            'departments' => $departments,
            'seats' => $seats,
            'rooms' => $rooms,
            'studentId' => $studentId,
            'getMonths' => $getMonths,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Add Hall Allotment',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateHallAllotmentRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $hallAllotment = $this->hallAllotmentService->createHallAllotment($validatedData);
            return redirect()->route('hall-allotments.index')->with('success', 'Hall Allotment created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(HallAllotment $hallAllotment)
    {
        // Not used
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, HallAllotment $hallAllotment)
    {
        $breadcrumbs = Breadcrumbs::generate('createHallAllotment', $hallAllotment);
        $studentId = $request->query('studentId');

        // ✅ Use the new method for edit as well
        $students = $this->studentService->getAllStudents();

        $halls = $this->hallService->getHalls();
        $departments = $this->departmentService->getActiveDepartments();
        $seats = $this->seatService->getSeatsForEdit($hallAllotment->seat_id);
        $getMonths = $this->hallAllotmentService->getCurrentYearMonths();

        return Inertia::render('HallAllotment/Create', [
            'hallAllotment' => $hallAllotment,
            'students' => $students,
            'halls' => $halls,
            'departments' => $departments,
            'seats' => $seats,
            'studentId' => $studentId,
            'getMonths' => $getMonths,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Edit Hall Allotment',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHallAllotmentRequest $request, HallAllotment $hallAllotment)
    {
        $validatedData = $request->validated();

        try {
            $hallAllotment = $this->hallAllotmentService->updateHallAllotment($hallAllotment, $validatedData);
            return redirect()->route('hall-allotments.index')->with('success', 'Hall Allotment updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HallAllotment $hallAllotment)
    {
        try {
            $this->hallAllotmentService->deleteHallAllotment($hallAllotment);
            return redirect()->route('hall-allotments.index')->with('success', 'Hall Allotment deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Hall Allotment deletion failed');
        }
    }

    /**
     * ✅ Cancel hall allotment (instead of delete)
     */
    public function cancel(Request $request, HallAllotment $hallAllotment)
    {
        try {
            $endingMonth = $request->ending_month;
            $cancelledAllotment = $this->hallAllotmentService->cancelHallAllotment($hallAllotment->id, $endingMonth);

            return redirect()->route('hall-allotments.index')->with('success', 'Hall Allotment cancelled successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Request cancellation (not immediate)
     */
    public function requestCancel(Request $request, HallAllotment $hallAllotment)
    {
        try {
            $this->hallAllotmentService->requestCancel(
                $hallAllotment->id,
                $request->ending_month
            );
            return redirect()->route('hall-allotments.index')->with('success', 'Cancellation request submitted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Approve cancellation (Admin action)
     */
    public function approveCancel(HallAllotment $hallAllotment)
    {
        try {
            $this->hallAllotmentService->approveCancel($hallAllotment->id);
            return redirect()->route('hall-allotments.index')->with('success', 'Cancellation approved successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Get available seats for a hall and month
     */
    public function getAvailableSeats(Request $request)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'starting_month' => 'required|date'
        ]);

        $seats = $this->hallAllotmentService->getAvailableSeats(
            $request->hall_id,
            $request->starting_month
        );

        return response()->json(['seats' => $seats]);
    }
}
