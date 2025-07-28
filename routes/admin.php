<?php

// File: routes/admin.php
// Description: Admin specific routes for Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Added POST route for ILO reordering.
// -----------------------------------------------------------------------------

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\ManageFacultyAccountController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Middleware\AdminAuth;

// Admin Login View
Route::get('/login', function () {
    return view('auth.admin-login');
})->name('admin.login.form');

// Google Login & Callback
Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('admin.google.login');
Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('admin.google.callback');

// Profile Completion Form (Accessible even without complete profile)
Route::get('/complete-profile', [ProfileController::class, 'showCompleteForm'])->name('admin.complete-profile');
Route::post('/complete-profile', [ProfileController::class, 'submitProfile'])->name('admin.submit-profile');

// Protected Admin Routes (Requires role = admin and complete profile)
Route::middleware([AdminAuth::class])->group(function () {

    // Admin Dashboard
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');

    // Academic Structure Page
    Route::get('/academic-structure', [AcademicStructureController::class, 'index'])->name('admin.academic-structure.index');

    // Program Management (CRUD)
    Route::resource('programs', ProgramController::class)->names([
        'index'   => 'admin.programs.index',
        'create'  => 'admin.programs.create',
        'store'   => 'admin.programs.store',
        'edit'    => 'admin.programs.edit',
        'update'  => 'admin.programs.update',
        'destroy' => 'admin.programs.destroy',
        'show'    => 'admin.programs.show',
    ]);

    // Course Management (CRUD)
    Route::resource('courses', CourseController::class)->only(['store', 'update', 'destroy'])->names([
        'store'   => 'admin.courses.store',
        'update'  => 'admin.courses.update',
        'destroy' => 'admin.courses.destroy',
    ]);

    // ✅ Manage Faculty Accounts (Pending, Approve, Reject)
    Route::get('/manage-accounts', [ManageFacultyAccountController::class, 'index'])->name('admin.manage-accounts');
    Route::post('/manage-accounts/{id}/approve', [ManageFacultyAccountController::class, 'approve'])->name('admin.manage-accounts.approve');
    Route::post('/manage-accounts/{id}/reject', [ManageFacultyAccountController::class, 'reject'])->name('admin.manage-accounts.reject');

    // ✅ Master Data (SO & ILO)
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('admin.master-data.index');
    Route::post('/master-data/{type}', [MasterDataController::class, 'store'])->name('admin.master-data.store');
    Route::put('/master-data/{type}/{id}', [MasterDataController::class, 'update'])->name('admin.master-data.update');
    Route::delete('/master-data/{type}/{id}', [MasterDataController::class, 'destroy'])->name('admin.master-data.destroy');
    Route::post('/master-data/reorder/ilo', [MasterDataController::class, 'reorderIlo'])->name('admin.master-data.reorder.ilo');
    Route::post('/master-data/reorder/so', [MasterDataController::class, 'reorderSo'])->name('admin.master-data.reorder.so');

    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        return redirect()->route('admin.login.form')->with('success', 'Logged out successfully.');
    })->name('admin.logout');
});
