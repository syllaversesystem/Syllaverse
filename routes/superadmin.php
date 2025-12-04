<?php

// -----------------------------------------------------------------------------
// * File: routes/superadmin.php
// * Description: Super Admin specific routes for Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-11] Update – switched Appointments routes to model binding {appointment},
//              added DELETE /appointments/{appointment} (destroy) and standardized names.
// -----------------------------------------------------------------------------

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\ManageAdminController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\ChairRequestController;
use App\Http\Controllers\SuperAdmin\AppointmentController;
use App\Http\Controllers\SuperAdmin\DepartmentsController as SADepartmentsController;
use App\Http\Controllers\SuperAdmin\PendingAccountsController;
use App\Http\Controllers\SuperAdmin\ApprovedAccountsController;
use App\Http\Controllers\SuperAdmin\RejectedAccountsController;

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

    // ---------- Dashboard ----------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('superadmin.dashboard');

    // ✅ Modularized Manage Accounts View
    Route::get('/manage-accounts', [ManageAdminController::class, 'index'])->name('superadmin.manage-accounts');
    // Pending Accounts Module (Approvals extracted)
    Route::get('/pending-accounts', [PendingAccountsController::class, 'index'])->name('superadmin.pending-accounts');
    // Approved Accounts Module
    Route::get('/approved-accounts', [ApprovedAccountsController::class, 'index'])->name('superadmin.approved-accounts');
    // Rejected Accounts Module
    Route::get('/rejected-accounts', [RejectedAccountsController::class, 'index'])->name('superadmin.rejected-accounts');

    Route::view('/class-suspension', 'superadmin.class-suspension')->name('superadmin.class-suspension');
    Route::view('/system-logs', 'superadmin.system-logs')->name('superadmin.system-logs');
    Route::view('/notifications', 'superadmin.notifications')->name('superadmin.notifications');

    // ---------- Manage Admin Accounts ----------
    Route::post('/manage-accounts/admin/{id}/approve', [ManageAdminController::class, 'approve'])->name('superadmin.approve.admin');
    Route::post('/manage-accounts/admin/{id}/reject',  [ManageAdminController::class, 'reject'])->name('superadmin.reject.admin');
    Route::post('/manage-accounts/admin/{id}/suspend', [ManageAdminController::class, 'suspend'])->name('superadmin.suspend.admin');
    Route::post('/manage-accounts/admin/{id}/delete',  [ManageAdminController::class, 'delete'])->name('superadmin.delete.admin');

    // ---------- Manage Faculty Accounts ----------
    Route::post('/manage-accounts/faculty/{id}/approve', [ManageAdminController::class, 'approveFaculty'])->name('superadmin.approve.faculty');
    Route::post('/manage-accounts/faculty/{id}/reject', [ManageAdminController::class, 'rejectFaculty'])->name('superadmin.reject.faculty');
    Route::post('/manage-accounts/faculty/{id}/suspend', [ManageAdminController::class, 'suspendFaculty'])->name('superadmin.suspend.faculty');
    Route::post('/manage-accounts/faculty/{id}/reactivate', [ManageAdminController::class, 'reactivateFaculty'])->name('superadmin.reactivate.faculty');
    Route::post('/manage-accounts/faculty/{id}/delete', [ManageAdminController::class, 'deleteFaculty'])->name('superadmin.delete.faculty');
    Route::patch('/manage-accounts/{id}/revoke', [ManageAdminController::class, 'revoke'])->name('superadmin.accounts.revoke');

    // ---------- Chair Requests (Approve/Reject) ----------
    Route::post('/chair-requests/{id}/approve', [ChairRequestController::class, 'approve'])->name('superadmin.chair-requests.approve');
    Route::post('/chair-requests/{id}/reject',  [ChairRequestController::class, 'reject'])->name('superadmin.chair-requests.reject');
    Route::post('/chair-requests/user/{userId}/approve-all', [ChairRequestController::class, 'approveAll'])->name('superadmin.chair-requests.approve-all');
    Route::post('/chair-requests/user/{userId}/reject-all', [ChairRequestController::class, 'rejectAll'])->name('superadmin.chair-requests.reject-all');

    // ---------- Appointments (Create/Update/End/Destroy) ----------
    Route::post('/appointments',                   [AppointmentController::class, 'store'])->name('superadmin.appointments.store');
    Route::put('/appointments/{appointment}',      [AppointmentController::class, 'update'])->name('superadmin.appointments.update');
    Route::post('/appointments/{appointment}/end', [AppointmentController::class, 'end'])->name('superadmin.appointments.end');
    Route::delete('/appointments/{appointment}',   [AppointmentController::class, 'destroy'])->name('superadmin.appointments.destroy');

    // ---------- Departments Management (Superadmin) ----------
    Route::get('/departments', [SADepartmentsController::class, 'index'])->name('superadmin.departments.index');
    Route::get('/departments/table-content', [SADepartmentsController::class, 'tableContent'])->name('superadmin.departments.table-content');
    Route::post('/departments', [SADepartmentsController::class, 'store'])->name('superadmin.departments.store');
    Route::put('/departments/{department}', [SADepartmentsController::class, 'update'])->name('superadmin.departments.update');
    Route::delete('/departments/{department}', [SADepartmentsController::class, 'destroy'])->name('superadmin.departments.destroy');
});
