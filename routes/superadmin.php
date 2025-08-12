<?php
// -------------------------------------------------------------------------------
// * File: routes/superadmin.php
// * Description: Super Admin specific routes for Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-11] Update – switched Appointments routes to model binding {appointment},
//              added DELETE /appointments/{appointment} (destroy) and standardized names.
// [2025-08-12] Master Data – added POST /master-data/{type}/reorder for drag-to-reorder with renumbering.
// -------------------------------------------------------------------------------

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\DepartmentController;
use App\Http\Controllers\SuperAdmin\MasterDataController;
use App\Http\Controllers\SuperAdmin\ManageAdminController;
use App\Http\Controllers\SuperAdmin\ChairRequestController;
use App\Http\Controllers\SuperAdmin\AppointmentController;

use App\Http\Middleware\SuperAdminAuth;

// ---------- Public Super Admin Login ----------
Route::middleware('guest')->group(function () {
    Route::get('/superadmin/login', function () {
        return view('auth.superadmin-login');
    })->name('superadmin.login.form');

    Route::post('/superadmin/login', [AuthController::class, 'login'])->name('superadmin.login');
});

// ---------- Super Admin Protected Routes ----------
Route::middleware([SuperAdminAuth::class])->prefix('superadmin')->group(function () {

    // ---------- Logout ----------
    Route::post('/logout', [AuthController::class, 'logout'])->name('superadmin.logout');

    // ---------- Dashboard & Pages ----------
    Route::view('/dashboard', 'superadmin.dashboard')->name('superadmin.dashboard');

    // ✅ Modularized Manage Accounts View
    Route::get('/manage-accounts', [ManageAdminController::class, 'index'])->name('superadmin.manage-accounts');

    Route::view('/class-suspension', 'superadmin.class-suspension')->name('superadmin.class-suspension');
    Route::view('/system-logs', 'superadmin.system-logs')->name('superadmin.system-logs');
    Route::view('/notifications', 'superadmin.notifications')->name('superadmin.notifications');

    // ---------- Manage Admin Accounts ----------
    Route::post('/manage-accounts/admins/{id}/approve', [ManageAdminController::class, 'approve'])->name('superadmin.approve.admin');
    Route::post('/manage-accounts/admins/{id}/reject',  [ManageAdminController::class, 'reject'])->name('superadmin.reject.admin');

    // ---------- Chair Requests (Approve/Reject) ----------
    Route::post('/chair-requests/{id}/approve', [ChairRequestController::class, 'approve'])->name('superadmin.chair-requests.approve');
    Route::post('/chair-requests/{id}/reject',  [ChairRequestController::class, 'reject'])->name('superadmin.chair-requests.reject');

    // ---------- Appointments (Create/Update/End/Destroy) ----------
    Route::post('/appointments',                         [AppointmentController::class, 'store'])->name('superadmin.appointments.store');
    Route::put('/appointments/{appointment}',            [AppointmentController::class, 'update'])->name('superadmin.appointments.update');
    Route::post('/appointments/{appointment}/end',       [AppointmentController::class, 'end'])->name('superadmin.appointments.end');
    Route::delete('/appointments/{appointment}',         [AppointmentController::class, 'destroy'])->name('superadmin.appointments.destroy');

    // ---------- Master Data ----------
    Route::prefix('master-data')->group(function () {
        Route::get('/',                   [MasterDataController::class, 'index'])->name('superadmin.master-data');
        Route::post('/{type}',            [MasterDataController::class, 'store'])->name('superadmin.master-data.store');
        Route::put('/{type}/{id}',        [MasterDataController::class, 'update'])->name('superadmin.master-data.update');
        Route::delete('/{type}/{id}',     [MasterDataController::class, 'destroy'])->name('superadmin.master-data.destroy');

Route::post('/superadmin/master-data/reorder/{type}', [MasterDataController::class, 'reorder'])
    ->name('superadmin.master-data.reorder');
    });

    // ---------- General Academic Information ----------
    Route::put('/general-info/{section}', [MasterDataController::class, 'updateGeneralInfo'])->name('superadmin.general-info.update');

    // ---------- Departments ----------
    Route::prefix('departments')->group(function () {
        Route::get('/',        [DepartmentController::class, 'index'])->name('superadmin.departments.index');
        Route::post('/',       [DepartmentController::class, 'store'])->name('superadmin.departments.store');
        Route::put('/{id}',    [DepartmentController::class, 'update'])->name('superadmin.departments.update');
        Route::delete('/{id}', [DepartmentController::class, 'destroy'])->name('superadmin.departments.destroy');
    });
});
