<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hall\CreateHallRequest;
use App\Http\Requests\Hall\UpdateHallRequest;
use App\Models\Hall;
use App\Services\HallService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class HallController extends Controller implements HasMiddleware
{
    protected HallService $hallService;

    public function __construct(HallService $hallService)
    {
        $this->hallService = $hallService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-create-hall', only: ['create', 'store']),
            new Middleware('permission:can-edit-hall', only: ['edit', 'update', 'changeStatus']),
            new Middleware('permission:can-delete-hall', only: ['destroy']),
            new Middleware('permission:can-view-hall', only: ['index']),
            new Middleware('permission:can-view-details-hall', only: ['show']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('halls');
        $halls = $this->hallService->getHalls();
        $responseData = [
            'breadcrumbs' => $breadcrumbs,
            'halls' => $halls,
            'pageTitle' => 'Hall List',
        ];
        return Inertia::render('Hall/Index', $responseData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumbs = Breadcrumbs::generate('createHall');
        $responseData = [
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Create Hall',
        ];
        return Inertia::render('Hall/Create', $responseData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateHallRequest $request)
    {
        $validatedData = $request->validated();
        $hall = $this->hallService->create($validatedData);
        $status = $hall ? 'success' : 'error';
        $message = $hall ? 'Hall created successfully.' : 'Failed to create hall.';
        return redirect()->route('halls.index')->with($status, $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Hall $hall)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hall $hall)
    {
        $breadcrumbs = Breadcrumbs::generate('editHall', $hall);
        $responseData = [
            'breadcrumbs' => $breadcrumbs,
            'hall' => $hall,
            'pageTitle' => 'Edit Hall',
        ];
        return Inertia::render('Hall/Create', $responseData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHallRequest $request, Hall $hall)
    {
        $validatedData = $request->validated();
        $updateHall = $this->hallService->update($hall, $validatedData);
        $status = $updateHall ? 'success' : 'error';
        $message = $updateHall ? 'Hall updated successfully.' : 'Failed to update Hall.';
        return redirect()->route('halls.index')->with($status, $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hall $hall)
    {
        $deleted = $hall->delete();
        $status = $deleted ? 'success' : 'error';
        $message = $deleted ? 'Hall deleted successfully.' : 'Failed to delete hall.';
        return redirect()->route('halls.index')->with($status, $message);
    }

    public function changeStatus(Request $request, Hall $hall)
    {
        $hall = $this->hallService->changeStatus($hall, $request->is_active);
        $status = $hall ? "success" : "error";
        $message = $hall->is_active ? 'Hall is active' : 'Hall is deactivate';
        return redirect()->back()->with($status, $message);
    }
}
