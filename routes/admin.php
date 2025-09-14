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
// Note: do NOT register a global 'login' route here. Superadmin/admin/faculty
// use distinct login routes/names (e.g. 'superadmin.login.form', 'admin.login.form',
// 'faculty.login.form'). Use the admin guard for admin-only auth redirects.
/* ░░░ END: Admin Login (View) ░░░ */

/* ░░░ START: Google OAuth ░░░ */
Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('admin.google.login');
Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('admin.google.callback');
/* ░░░ END: Google OAuth ░░░ */

/* ░░░ START: Complete Profile (PENDING or ACTIVE admins) ░░░ */
Route::middleware('auth:admin')->group(function () {
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

    // Admin Syllabi (view & export) — mirrors faculty routes but for admins
    Route::get('/syllabi/create', [\App\Http\Controllers\Admin\SyllabusController::class, 'create'])->name('admin.syllabi.create');
    Route::post('/syllabi', [\App\Http\Controllers\Admin\SyllabusController::class, 'store'])->name('admin.syllabi.store');
    Route::get('/syllabi', [\App\Http\Controllers\Admin\SyllabusController::class, 'index'])->name('admin.syllabi.index');
    Route::get('/syllabi/{id}', [\App\Http\Controllers\Admin\SyllabusController::class, 'show'])->name('admin.syllabi.show');
    Route::get('/syllabi/{id}/export/pdf', [\App\Http\Controllers\Admin\SyllabusController::class, 'exportPdf'])->name('admin.syllabi.export.pdf');
    Route::get('/syllabi/{id}/export/word', [\App\Http\Controllers\Admin\SyllabusController::class, 'exportWord'])->name('admin.syllabi.export.word');
    Route::put('/syllabi/{id}', [\App\Http\Controllers\Admin\SyllabusController::class, 'update'])->name('admin.syllabi.update');
    // Admin ILO routes (mirror faculty ILO endpoints so the partial's route names resolve)
    Route::put('/syllabi/{syllabus}/ilos', [\App\Http\Controllers\Faculty\SyllabusIloController::class, 'update'])->name('admin.syllabi.ilos.update');
    Route::post('/syllabi/ilos/store', [\App\Http\Controllers\Faculty\SyllabusIloController::class, 'store'])->name('admin.syllabi.ilos.store');
    Route::put('/syllabi/ilos/{syllabus}/{ilo}', [\App\Http\Controllers\Faculty\SyllabusIloController::class, 'inlineUpdate'])->name('admin.syllabi.ilos.inline');
    Route::delete('/syllabi/ilos/{id}', [\App\Http\Controllers\Faculty\SyllabusIloController::class, 'destroy'])->name('admin.syllabi.ilos.destroy');
    Route::post('/syllabi/reorder/ilo', [\App\Http\Controllers\Faculty\SyllabusIloController::class, 'reorder'])->name('admin.syllabi.ilos.reorder');
    Route::post('/syllabi/{syllabus}/assessment-tasks', [\App\Http\Controllers\Admin\SyllabusController::class, 'saveAssessmentTasks'])->name('admin.syllabi.assessment_tasks.save');
    Route::post('/syllabi/{syllabus}/assessment-mappings', [\App\Http\Controllers\Admin\SyllabusController::class, 'saveAssessmentMappings'])->name('admin.syllabi.assessment_mappings.save');
    Route::delete('/syllabi/{id}', [\App\Http\Controllers\Admin\SyllabusController::class, 'destroy'])->name('admin.syllabi.destroy');

    // IGA (Institutional Graduate Attributes) mirrors
    Route::put('/syllabi/{syllabus}/igas', [\App\Http\Controllers\Faculty\SyllabusIgaController::class, 'update'])->name('admin.syllabi.iga.update');
    Route::post('/syllabi/igas/reorder', [\App\Http\Controllers\Faculty\SyllabusIgaController::class, 'reorder'])->name('admin.syllabi.iga.reorder');
    Route::delete('/syllabi/igas/{id}', [\App\Http\Controllers\Faculty\SyllabusIgaController::class, 'destroy'])->name('admin.syllabi.iga.destroy');

    // CDIO mirrors
    Route::put('/syllabi/{syllabus}/cdios', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'update'])->name('admin.syllabi.cdios.update');
    Route::post('/syllabi/{syllabus}/cdios/reorder', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'reorder'])->name('admin.syllabi.cdios.reorder');
    Route::post('/syllabi/cdios', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'store'])->name('admin.syllabi.cdios.store');
    Route::put('/syllabi/{syllabus}/cdios/{cdio}', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'inlineUpdate'])->name('admin.syllabi.cdios.inline');
    Route::delete('/syllabi/cdios/{id}', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'destroy'])->name('admin.syllabi.cdios.destroy');

    // SO mirrors
    Route::put('/syllabi/{syllabus}/sos', [\App\Http\Controllers\Faculty\SyllabusSoController::class, 'update'])->name('admin.syllabi.sos.update');
    Route::post('/syllabi/{syllabus}/sos/reorder', [\App\Http\Controllers\Faculty\SyllabusSoController::class, 'reorder'])->name('admin.syllabi.sos.reorder');
    Route::delete('/syllabi/sos/{id}', [\App\Http\Controllers\Faculty\SyllabusSoController::class, 'destroy'])->name('admin.syllabi.sos.destroy');

    // SDG mirrors
    Route::post('/syllabi/{syllabus}/sdgs', [\App\Http\Controllers\Faculty\SyllabusSdgController::class, 'attach'])->name('admin.syllabi.sdgs.attach');
    Route::put('/syllabi/{syllabus}/sdgs', [\App\Http\Controllers\Faculty\SyllabusSdgController::class, 'bulkUpdate'])->name('admin.syllabi.sdgs.save');
    Route::post('/syllabi/{syllabus}/sdgs/reorder', [\App\Http\Controllers\Faculty\SyllabusSdgController::class, 'reorder'])->name('admin.syllabi.sdgs.reorder');
    Route::put('/syllabi/{syllabus}/sdgs/update/{pivot}', [\App\Http\Controllers\Faculty\SyllabusSdgController::class, 'update'])->name('admin.syllabi.sdgs.update');
    Route::delete('/syllabi/{syllabus}/sdgs/{sdg}', [\App\Http\Controllers\Faculty\SyllabusSdgController::class, 'detach'])->name('admin.syllabi.sdgs.detach');
    Route::delete('/syllabi/{syllabus}/sdgs/entry/{id}', [\App\Http\Controllers\Faculty\SyllabusSdgController::class, 'destroyEntry'])->name('admin.syllabi.sdgs.destroy_entry');

    // Textbook mirrors
    Route::post('/syllabi/{syllabus}/textbook', [\App\Http\Controllers\Faculty\SyllabusTextbookController::class, 'store'])->name('admin.syllabi.textbook.upload');
    Route::delete('/syllabi/textbook/{textbook}', [\App\Http\Controllers\Faculty\SyllabusTextbookController::class, 'destroy'])->name('admin.syllabi.textbook.delete');
    Route::put('/syllabi/textbook/{textbook}', [\App\Http\Controllers\Faculty\SyllabusTextbookController::class, 'update'])->name('admin.syllabi.textbook.update');
    Route::get('/syllabi/{syllabus}/textbook/list', [\App\Http\Controllers\Faculty\SyllabusTextbookController::class, 'list'])->name('admin.syllabi.textbook.list');

    // TLA mirrors
    Route::post('/syllabi/{id}/tla', [\App\Http\Controllers\Faculty\SyllabusTLAController::class, 'update'])->name('admin.syllabi.tla.update');
    Route::post('/syllabi/{id}/tla/append', [\App\Http\Controllers\Faculty\SyllabusTLAController::class, 'append'])->name('admin.syllabi.tla.append');
    Route::delete('/syllabi/tla/{id}', [\App\Http\Controllers\Faculty\SyllabusTLAController::class, 'destroy'])->name('admin.syllabi.tla.delete');
    Route::match(['get', 'post'], '/syllabi/tla/{id}/sync-ilo', [\App\Http\Controllers\Faculty\SyllabusTLAController::class, 'syncIlo'])->name('admin.syllabi.tla.sync-ilo');
    Route::match(['get', 'post'], '/syllabi/tla/{id}/sync-so', [\App\Http\Controllers\Faculty\SyllabusTLAController::class, 'syncSo'])->name('admin.syllabi.tla.sync-so');
    Route::post('/syllabi/{syllabus}/generate-tla', [\App\Http\Controllers\Faculty\SyllabusTLAController::class, 'generateWithAI'])
        ->name('admin.syllabi.tla.generate');

    // Logout
    Route::post('/logout', function () {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login.form');
    })->name('admin.logout');
});
/* ░░░ END: Protected Admin Routes ░░░ */
