<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Inertia\Inertia;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-view-dashboard', only: ['index']),
        ];
    }
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard
     */
    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('dashboard');
        $permissions = session('permissions');
        
        // Get all dashboard data
        $dashboardData = $this->dashboardService->getDashboardData();
        
        return Inertia::render('Dashboard', [
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.home'),
            'permissions' => $permissions,
            'dashboardData' => $dashboardData,
        ]);
    }
}
