<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomType\CreateRoomTypeRequest;
use App\Http\Requests\RoomType\UpdateRoomTypeRequest;
use App\Models\RoomType;
use App\Services\RoomTypeService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class RoomTypeController extends Controller implements HasMiddleware
{
    protected RoomTypeService $roomTypeService;

    public function __construct(RoomTypeService $roomTypeService)
    {
        $this->roomTypeService = $roomTypeService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-create-room-type', only: ['create', 'store']),
            new Middleware('permission:can-edit-room-type', only: ['edit', 'update', 'changeStatus']),
            new Middleware('permission:can-delete-room-type', only: ['destroy']),
            new Middleware('permission:can-view-room-type', only: ['index']),
            new Middleware('permission:can-view-details-room-type', only: ['show']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('roomTypes');
        $roomTypes = $this->roomTypeService->getRoomTypeByProvost();
        $responseData = [
            'breadcrumbs' => $breadcrumbs,
            'roomTypes' => $roomTypes,
            'pageTitle' => 'Room Type List',
        ];
        return Inertia::render('RoomType/Index', $responseData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumbs = Breadcrumbs::generate('createRoomType');
        $responseData = [
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Add Room Type',
        ];
        return Inertia::render('RoomType/Create', $responseData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRoomTypeRequest $request)
    {
        $validatedData = $request->validated();
        $roomType = $this->roomTypeService->createRoomType($validatedData);
        $status = $roomType ? 'success' : 'error';
        $message = $roomType ? 'Room type created successfully.' : 'Failed to create room type.';
        return redirect()->route('room-types.index')->with($status, $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomType $roomType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomType $roomType)
    {
        $breadcrumbs = Breadcrumbs::generate('editRoomType', $roomType);
        $responseData = [
            'roomType' => $roomType,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Update Room Type',
        ];
        return Inertia::render('RoomType/Create', $responseData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomTypeRequest $request, RoomType $roomType)
    {
        $validatedData = $request->validated();
        $updateHall = $this->roomTypeService->updateRoomType($roomType, $validatedData);
        $status = $updateHall ? 'success' : 'error';
        $message = $updateHall ? 'Room type updated successfully.' : 'Failed to update room type.';
        return redirect()->route('room-types.index')->with($status, $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomType $roomType)
    {
        $deleted = $roomType->delete();
        $status = $deleted ? 'success' : 'error';
        $message = $deleted ? 'Room Type deleted successfully.' : 'Failed to delete room type.';
        return redirect()->route('room-types.index')->with($status, $message);
    }

    public function changeStatus(Request $request, RoomType $roomType)
    {
        $hall = $this->roomTypeService->changeStatus($roomType, $request->is_active);
        $status = $hall ? "success" : "error";
        $message = $hall->is_active ? 'Room Type is active' : 'Room Type is deactivate';
        return redirect()->back()->with($status, $message);
    }
}
