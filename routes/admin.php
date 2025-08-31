<?php

// -------------------------------------------------------------------------------
// * File: routes/admin.php
// * Description: Admin-specific routes (Google OAuth, profile, protected admin area) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Updated flow: allow pending admins to log in and access Complete Profile; fixed controller method; added auth middleware to profile routes.
// [2025-08-08] Restored Master Data routes (index/store/update/destroy + ILO/SO reorder).
// [2025-08-16] Added explicit approve/reject faculty routes with correct names for manage-accounts tabs.
// [2025-08-17] Cleaned up Program/Course resource routes → only store/update/destroy, since index comes from MasterDataController.
// [2025-08-18] Synced ProgramController routes with AJAX modals (store/update/destroy only).
// [2025-08-18] 🔁 Update – Removed SO/ILO CRUD from MasterDataController; wired SO CRUD + reorder to StudentOutcomeController. Kept ILO reorder on MasterDataController.
// [2025-08-18] ✅ Organize – Added ILO CRUD (IntendedLearningOutcomeController) + AJAX fetch; grouped routes cleanly.
// -------------------------------------------------------------------------------

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\ManageFacultyAccountController;
use App\Http\Controllers\Admin\MasterDataController;

use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CourseController;

use App\Http\Controllers\Admin\StudentOutcomeController;
use App\Http\Controllers\Admin\IntendedLearningOutcomeController;

use App\Http\Middleware\AdminAuth;

/* ░░░ START: Admin Login (View) ░░░ */
Route::get('/login', function () {
    return view('auth.admin-login');
})->name('admin.login.form');
/* ░░░ END: Admin Login (View) ░░░ */

/* ░░░ START: Google OAuth ░░░ */
Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('admin.google.login');
Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('admin.google.callback');
/* ░░░ END: Google OAuth ░░░ */

/* ░░░ START: Complete Profile (PENDING or ACTIVE admins) ░░░ */
Route::middleware('auth')->group(function () {
    Route::get('/complete-profile', [ProfileController::class, 'showCompleteProfile'])->name('admin.complete-profile');
    Route::post('/complete-profile', [ProfileController::class, 'submitProfile'])->name('admin.submit-profile');
});
/* ░░░ END: Complete Profile ░░░ */

/* ░░░ START: Protected Admin Routes ░░░ */
Route::middleware([AdminAuth::class])->group(function () {

    // Dashboard
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');

    // Academic Structure
    Route::get('/academic-structure', [AcademicStructureController::class, 'index'])
        ->name('admin.academic-structure.index');

    // ───────────────────────────────────────────────────────────────────────────
    // Master Data (Page Composition + AJAX fetch)
    // ───────────────────────────────────────────────────────────────────────────
    Route::get('/master-data', [MasterDataController::class, 'index'])
        ->name('admin.master-data.index');

    // AJAX: fetch ILOs by course (used by dropdown loader)
    Route::get('/master-data/ilos', [MasterDataController::class, 'fetchIlos'])
        ->name('admin.master-data.ilos.index');

// ILO reorder (move off MasterDataController)
Route::post('/master-data/reorder/ilo', [\App\Http\Controllers\Admin\IntendedLearningOutcomeController::class, 'reorder'])
    ->name('admin.ilo.reorder');


    // ───────────────────────────────────────────────────────────────────────────
    // Student Outcomes (SO) – dedicated controller (CRUD + reorder)
    // ───────────────────────────────────────────────────────────────────────────
    Route::post('/master-data/so',        [StudentOutcomeController::class, 'store'])->name('admin.so.store');
    Route::put('/master-data/so/{id}',    [StudentOutcomeController::class, 'update'])->name('admin.so.update');
    Route::delete('/master-data/so/{id}', [StudentOutcomeController::class, 'destroy'])->name('admin.so.destroy');
    Route::post('/master-data/reorder/so',[StudentOutcomeController::class, 'reorder'])->name('admin.so.reorder');

    // ───────────────────────────────────────────────────────────────────────────
    // Intended Learning Outcomes (ILO) – dedicated controller (CRUD)
    // ───────────────────────────────────────────────────────────────────────────
    Route::post('/master-data/ilo',        [IntendedLearningOutcomeController::class, 'store'])->name('admin.ilo.store');
    Route::put('/master-data/ilo/{id}',    [IntendedLearningOutcomeController::class, 'update'])->name('admin.ilo.update');
    Route::delete('/master-data/ilo/{id}', [IntendedLearningOutcomeController::class, 'destroy'])->name('admin.ilo.destroy');

    // ───────────────────────────────────────────────────────────────────────────
    // Programs (AJAX modals)
    // ───────────────────────────────────────────────────────────────────────────
    Route::post('/programs',        [ProgramController::class, 'store'])->name('admin.programs.store');
    Route::put('/programs/{id}',    [ProgramController::class, 'update'])->name('admin.programs.update');
    Route::delete('/programs/{id}', [ProgramController::class, 'destroy'])->name('admin.programs.destroy');

    // ───────────────────────────────────────────────────────────────────────────
    // Courses (AJAX forms)
    // ───────────────────────────────────────────────────────────────────────────
    Route::resource('courses', CourseController::class)
        ->only(['store', 'update', 'destroy'])
        ->names([
            'store'   => 'admin.courses.store',
            'update'  => 'admin.courses.update',
            'destroy' => 'admin.courses.destroy',
        ]);

    // ───────────────────────────────────────────────────────────────────────────
    // Manage Faculty Accounts
    // ───────────────────────────────────────────────────────────────────────────
    Route::get('/manage-accounts',                 [ManageFacultyAccountController::class, 'index'])->name('admin.manage-accounts');
    Route::post('/manage-accounts/{id}/approve',   [ManageFacultyAccountController::class, 'approve'])->name('admin.manage-accounts.approve');
    Route::post('/manage-accounts/{id}/reject',    [ManageFacultyAccountController::class, 'reject'])->name('admin.manage-accounts.reject');

    // Logout
    Route::post('/logout', function () {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login.form');
    })->name('admin.logout');
});
/* ░░░ END: Protected Admin Routes ░░░ */
