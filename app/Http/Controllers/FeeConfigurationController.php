<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Models\FeeConfiguration;
use App\Models\Hall;
use App\Services\FeeConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FeeConfigurationController extends Controller implements HasMiddleware
{
    public function __construct(
        protected FeeConfigurationService $feeConfigurationService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-view-fee-configuration', only: ['index', 'show']),
            new Middleware('permission:can-create-fee-configuration', only: ['store']),
            new Middleware('permission:can-edit-fee-configuration', only: ['update']),
            new Middleware('permission:can-delete-fee-configuration', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of fee configurations.
     */
    public function index(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('feeConfigurations');
        $user = Auth::user();
        
        $filters = $request->only(['search', 'hall_id', 'fee_type', 'is_active']);
        $feeConfigurations = $this->feeConfigurationService->getFeeConfigurations($filters, $user);

        // Get halls for filter dropdown and modal
        $halls = $user->hasRole(Constants::ROLE_SUPER_ADMIN) 
            ? Hall::all() 
            : Hall::whereIn('id', $user->halls ?? [])->get();

        $feeTypes = FeeConfiguration::getFeeTypes();

        return Inertia::render('FeeConfiguration/Index', [
            'feeConfigurations' => $feeConfigurations,
            'halls' => $halls,
            'feeTypes' => $feeTypes,
            'filters' => $filters,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Fee Configurations',
        ]);
    }

    /**
     * Store a newly created fee configuration.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hall_id' => 'nullable|exists:halls,id',
            'fee_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,semester,yearly,one_time',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $user = Auth::user();

        // Authorization check: ensure user can manage this hall
        if (!empty($validated['hall_id']) && !$user->hasRole(Constants::ROLE_SUPER_ADMIN)) {
            $userHallIds = $user->halls ?? [];
            if (!in_array($validated['hall_id'], $userHallIds)) {
                return redirect()->back()->withErrors(['hall_id' => 'You do not have permission to configure fees for this hall.']);
            }
        }

        $this->feeConfigurationService->createFeeConfiguration($validated, $user);

        return redirect()->route('fee-configurations.index')->with('success', 'Fee configuration created successfully.');
    }

    /**
     * Update the specified fee configuration.
     */
    public function update(Request $request, FeeConfiguration $feeConfiguration)
    {
        $validated = $request->validate([
            'hall_id' => 'nullable|exists:halls,id',
            'fee_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,semester,yearly,one_time',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $user = Auth::user();

        // Authorization check
        if (!empty($validated['hall_id']) && !$user->hasRole(Constants::ROLE_SUPER_ADMIN)) {
            $userHallIds = $user->halls ?? [];
            if (!in_array($validated['hall_id'], $userHallIds)) {
                return redirect()->back()->withErrors(['hall_id' => 'You do not have permission to configure fees for this hall.']);
            }
        }

        $this->feeConfigurationService->updateFeeConfiguration($feeConfiguration, $validated, $user);

        return redirect()->route('fee-configurations.index')->with('success', 'Fee configuration updated successfully.');
    }

    /**
     * Remove the specified fee configuration.
     */
    public function destroy(FeeConfiguration $feeConfiguration)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($feeConfiguration->hall_id && !$user->hasRole(Constants::ROLE_SUPER_ADMIN)) {
            $userHallIds = $user->halls ?? [];
            if (!in_array($feeConfiguration->hall_id, $userHallIds)) {
                abort(403, 'You do not have permission to delete this fee configuration.');
            }
        }

        $this->feeConfigurationService->deleteFeeConfiguration($feeConfiguration);

        return redirect()->route('fee-configurations.index')->with('success', 'Fee configuration deleted successfully.');
    }
}
