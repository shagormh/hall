<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HallAllotmentController;
use App\Http\Controllers\HallAttachmentController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\StudentController;
use App\Models\Department;
use Inertia\Inertia;
use App\Http\Middleware\Language;
use Illuminate\Support\Facades\Route;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Foundation\Application;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\Permission\RoleController;
use App\Http\Controllers\Permission\PermissionController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/localization/{locale}', [LocalizationController::class, 'localization'])->name('localization');
Route::get('/language-options', [LanguageController::class, 'getLanguageOptions'])->name('getLanguageOptions');


Route::middleware(Language::class)
    ->group(function () {

    Route::get('/', function () {
        return Inertia::render('Auth/Login', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'pageTitle' => __('pageTitle.custom.login'),
        ]);
    })->middleware('guest');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::middleware('auth')->group(function () {
        // Profile related routes
        Route::prefix('profile')->group(function() {
            Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        // Permission related routes
        Route::resource('permissions', PermissionController::class)->except('show', 'destroy');
        Route::patch('permissions/{permission}/change-status', [PermissionController::class, 'changeStatus'])->name('permissions.changeStatus');

        // Roles related routes
        Route::resource('roles', RoleController::class);
        Route::post('assign-permission', [RoleController::class, 'assignPermissionToRole']);
        Route::delete('remove-assigned-permission', [RoleController::class, 'removePermissionFromRole']);
        Route::prefix('roles/{role}')->group(function() {
            Route::patch('change-status', [RoleController::class, 'changeStatus'])->name('roles.changeStatus');
            Route::delete('remove-user/{user}', [RoleController::class, 'removeUserFromRole'])->name('roles.removeUserFromRole');
        });

        // User related routes
        Route::resource('/users', UserController::class);
        Route::prefix('users/{user}')->group(function() {
            Route::patch('update-details', [UserController::class, 'updateDetails'])->name('users.updateDetails');
            Route::patch('update-email', [UserController::class, 'updateEmail'])->name('users.updateEmail');
            Route::patch('update-roles', [UserController::class, 'updateRoles'])->name('users.updateRoles');
            Route::patch('update-password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
            Route::patch('change-status', [UserController::class, 'changeStatus'])->name('users.changeStatus');
        });
        // Add students.status route
        Route::patch('/students/{student}/status', [StudentController::class, 'updateStatus'])->name('students.status');

        //student related routes
        // Block list routes handled by StudentBlockListController (define before resource to avoid route param conflicts)
        Route::get('/students/block-list', [App\Http\Controllers\StudentBlockListController::class, 'index'])->name('students.blockList');
        Route::get('/students/block-list/{id}', [App\Http\Controllers\StudentBlockListController::class, 'show'])->name('students.blockList.show');
        Route::patch('/students/block-list/{id}/unblock', [App\Http\Controllers\StudentBlockListController::class, 'unblock'])->name('students.blockList.unblock');
        Route::delete('/students/block-list/{id}', [App\Http\Controllers\StudentBlockListController::class, 'destroy'])->name('students.blockList.destroy');
        Route::resource('students', StudentController::class);

        //Department related routes
        Route::resource('departments', DepartmentController::class);
        Route::patch('/departments/{department}/change-status', [DepartmentController::class, 'changeStatus'])->name('departments.changeStatus');

        //Hall Route
        Route::resource('halls', HallController::class);
        Route::patch('/halls/{hall}/change-status', [HallController::class, 'changeStatus'])->name('halls.changeStatus');

        //Room Route
        Route::resource('rooms', RoomController::class);

        //Hall Attachment Route
        Route::resource('hall-attachments', HallAttachmentController::class);
        Route::post('hall-attachments/student', [StudentController::class,'store'])->name('hallAttachments.student.store');
        Route::patch('/hall-attachment/{hallAttachment}/change-status', [HallAttachmentController::class, 'changeStatus'])->name('hall-attachments.changeStatus');

       // Hall Allotment Routes
        Route::get('/hall-allotments-report', [HallAllotmentController::class, 'report'])->name('hall-allotments.report');
        Route::get('/hall-allotments/check-student-eligibility', [HallAllotmentController::class, 'checkStudentEligibility'])->name('hall-allotments.check-student-eligibility');
        Route::get('/hall-allotments/available-seats', [HallAllotmentController::class, 'getAvailableSeats'])->name('hall-allotments.available-seats');
        Route::resource('hall-allotments', HallAllotmentController::class);
        Route::post('/hall-allotments/{hallAllotment}/cancel', [HallAllotmentController::class, 'cancel'])->name('hall-allotments.cancel');
        Route::post('/hall-allotments/{hallAllotment}/request-cancel', [HallAllotmentController::class, 'requestCancel'])->name('hall-allotments.request-cancel');
        Route::post('/hall-allotments/{hallAllotment}/approve-cancel', [HallAllotmentController::class, 'approveCancel'])->name('hall-allotments.approve-cancel');
        Route::post('/hall-allotments/{hallAllotment}/approve-cancel', [HallAllotmentController::class, 'approveCancel'])->name('hall-allotments.approve-cancel');
        //Room Type Route
        Route::resource('room-types', RoomTypeController::class);
        Route::patch('/room-types/{roomType}/change-status', [RoomTypeController::class, 'changeStatus'])->name('room-types.changeStatus');

        // Student Fee Routes
        Route::get('student-fees/checker', [\App\Http\Controllers\StudentFeeController::class, 'checker'])->name('student-fees.checker');
        Route::get('api/student-fees/search-checker', [\App\Http\Controllers\StudentFeeController::class, 'searchStudents'])->name('student-fees.search-checker');
        Route::get('api/student-fees/summary/{studentId}', [\App\Http\Controllers\StudentFeeController::class, 'getSummary'])->name('student-fees.get-summary');
        
        Route::resource('student-fees', \App\Http\Controllers\StudentFeeController::class);
        Route::post('student-fees/parse-voucher', [\App\Http\Controllers\StudentFeeController::class, 'parseVoucher'])->name('student-fees.parse-voucher');
        Route::patch('student-fees/{studentFee}/update-status', [\App\Http\Controllers\StudentFeeController::class, 'updateStatus'])->name('student-fees.update-status');

        // Fee Configuration Routes
        Route::resource('fee-configurations', \App\Http\Controllers\FeeConfigurationController::class)->except(['create', 'edit']);
    });
});


require __DIR__ . '/auth.php';

