<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;


use App\Services\AuthenticationLogService;
use App\Services\ActivityLogService;
use App\Services\Permission\RoleService;

class ProfileController extends Controller
{
    protected AuthenticationLogService $authenticationLogService;
    protected ActivityLogService $activityLogService;
    protected RoleService $roleService;

    public function __construct(
        AuthenticationLogService $authenticationLogService,
        ActivityLogService $activityLogService,
        RoleService $roleService
        
    ) {
        $this->authenticationLogService = $authenticationLogService;
        $this->activityLogService = $activityLogService;
        $this->roleService = $roleService;
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $breadcrumbs = Breadcrumbs::generate('myProfile', $user);

        $authenticationLogs = $this->authenticationLogService->getAuthenticationLogs($user);
        // $activityLogs = $this->activityLogService->getActivityLogs($user); // Optional, assuming redundant for now or can be added if needed
        $roles = $this->roleService->getActiveRoles(); // Needed for User/Show props

        $responseData = [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => $user->load(['roles']),
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.user.profile'),
            'authenticationLogs' => $authenticationLogs,
            'roles' => $roles,
            // 'activityLogs' => $activityLogs,
        ];

        // If user is a student, include student details
        if ($user->hasRole('Student')) {
            $student = \App\Models\Student::with([
                'hall',
                'department',
                'activeAllotment' => function($q) {
                    $q->with(['seat' => function($sq) {
                        $sq->with('room');
                    }]);
                }
            ])->where('user_id', $user->id)->first();

            if ($student) {
                // Get fee records
                $fees = \App\Models\StudentFee::where('student_id', $student->id)
                    ->orderByDesc('created_at')
                    ->get();

                $responseData['studentDetails'] = [
                    'student' => $student,
                    'fees' => $fees,
                ];
            }
        }

        // If user is hall provost or house tutor, include assigned halls
        if ($user->hasRole(['hall provost', 'house tutor'])) {
            $assignedHalls = $user->halls;
            // Fetch hall models if needed, but user.halls cast to array in model, need actual objects?
            // In DashboardService it was used as IDs. In User/Show it expects Name and IsActive.
            // User model says: 'halls' => 'array'. It contains IDs.
            // We need to fetch Hall objects.

            if (!empty($user->halls)) {
                $assignedHallsObjects = \App\Models\Hall::whereIn('id', $user->halls)->get();
                $responseData['assignedHalls'] = $assignedHallsObjects;
            } else {
                 $responseData['assignedHalls'] = [];
            }
        }

        return Inertia::render('Profile/Edit', $responseData);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $isUpdated = $request->user()->save();
        $status = $isUpdated ? Constants::SUCCESS : Constants::ERROR;
        $message = $isUpdated ? __('message.custom.user.update.profile.success') : __('message.custom.user.update.profile.error');
        return Redirect::route('profile.edit')->with($status, $message);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
