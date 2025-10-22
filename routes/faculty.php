<?php

// -----------------------------------------------------------------------------
// File: routes/faculty.php
// Description: Faculty-specific routes for login, syllabus CRUD, textbook, ILO, SO, TLA, SDG mapping, and export – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Integrated TLA ↔ ILO and SO sync routes into full faculty route structure.
// [2025-07-29] Fixed sync-ilo and sync-so to accept both GET and POST methods.
// -----------------------------------------------------------------------------

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Faculty\AuthController as FacultyAuthController;
use App\Http\Controllers\Faculty\ProfileController;
use App\Http\Controllers\Faculty\SyllabusController;
use App\Http\Controllers\Faculty\SyllabusTextbookController;
use App\Http\Controllers\Faculty\SyllabusTLAController;
use App\Http\Controllers\Faculty\SyllabusIloController;
use App\Http\Controllers\Faculty\SyllabusSoController;
use App\Http\Controllers\Faculty\SyllabusSdgController;
use App\Http\Controllers\Faculty\ManageFacultyAccountController;
use App\Http\Controllers\Faculty\DepartmentsController;
use App\Http\Controllers\Faculty\ProgramController;
use App\Http\Controllers\Faculty\CourseController;
use App\Http\Middleware\FacultyAuth;

// ---------- Faculty Login Form View ----------
Route::get('/faculty/login', function () {
    return view('auth.faculty-login');
})->name('faculty.login.form');

// ---------- Google Login ----------
Route::get('/faculty/login/google', [FacultyAuthController::class, 'redirectToGoogle'])->name('faculty.google.login');
Route::get('/faculty/google/callback', [FacultyAuthController::class, 'handleGoogleCallback'])->name('faculty.google.callback');

// ---------- Faculty Profile Completion (use faculty guard so faculty redirects target faculty login) ----------
Route::middleware(['auth:faculty'])->group(function () {
    Route::get('/faculty/complete-profile', [ProfileController::class, 'showCompleteProfile'])->name('faculty.complete-profile');
    Route::post('/faculty/complete-profile', [ProfileController::class, 'submitProfile'])->name('faculty.submit-profile');
});

// ---------- Faculty-Only Protected Routes ----------
Route::middleware([FacultyAuth::class])->group(function () {
    Route::view('/faculty/dashboard', 'faculty.dashboard')->name('faculty.dashboard');

    // ---------- Manage Faculty Accounts ----------
    Route::get('/faculty/manage-accounts', [ManageFacultyAccountController::class, 'index'])->name('faculty.manage-accounts.index');
    Route::post('/faculty/manage-accounts/{id}/approve', [ManageFacultyAccountController::class, 'approve'])->name('faculty.manage-accounts.approve');
    Route::post('/faculty/manage-accounts/{id}/reject', [ManageFacultyAccountController::class, 'reject'])->name('faculty.manage-accounts.reject');

    // ---------- Departments Management ----------
    Route::get('/faculty/departments', [DepartmentsController::class, 'index'])->name('faculty.departments.index');
    Route::post('/faculty/departments', [DepartmentsController::class, 'store'])->name('faculty.departments.store');
    Route::put('/faculty/departments/{department}', [DepartmentsController::class, 'update'])->name('faculty.departments.update');
    Route::delete('/faculty/departments/{department}', [DepartmentsController::class, 'destroy'])->name('faculty.departments.destroy');
    Route::get('/faculty/departments/table-content', [DepartmentsController::class, 'tableContent'])->name('faculty.departments.table-content');

    // ---------- Programs Management ----------
    Route::get('/faculty/programs', [ProgramController::class, 'index'])->name('faculty.programs.index');
    Route::post('/faculty/programs', [ProgramController::class, 'store'])->name('faculty.programs.store');
    Route::put('/faculty/programs/{program}', [ProgramController::class, 'update'])->name('faculty.programs.update');
    Route::delete('/faculty/programs/{program}', [ProgramController::class, 'destroy'])->name('faculty.programs.destroy');
    Route::get('/faculty/programs/search-deleted', [ProgramController::class, 'searchDeleted'])->name('faculty.programs.search-deleted');

    // ---------- Courses Management ----------
    Route::get('/faculty/courses', [CourseController::class, 'index'])->name('faculty.courses.index');
    Route::post('/faculty/courses', [CourseController::class, 'store'])->name('faculty.courses.store');
    Route::put('/faculty/courses/{course}', [CourseController::class, 'update'])->name('faculty.courses.update');
    Route::delete('/faculty/courses/{course}', [CourseController::class, 'destroy'])->name('faculty.courses.destroy');
    Route::get('/faculty/courses/search-deleted', [CourseController::class, 'searchDeleted'])->name('faculty.courses.search-deleted');

    // ---------- Syllabus Routes ----------
    Route::get('/faculty/syllabi', [SyllabusController::class, 'index'])->name('faculty.syllabi.index');
    Route::get('/faculty/syllabi/create', [SyllabusController::class, 'create'])->name('faculty.syllabi.create');
    Route::post('/faculty/syllabi', [SyllabusController::class, 'store'])->name('faculty.syllabi.store');
    Route::get('/faculty/syllabi/proceed', [SyllabusController::class, 'proceed'])->name('faculty.syllabi.proceed');
    Route::get('/faculty/syllabi/{id}', [SyllabusController::class, 'show'])->name('faculty.syllabi.show');
    Route::put('/faculty/syllabi/{id}', [SyllabusController::class, 'update'])->name('faculty.syllabi.update');
    Route::delete('/faculty/syllabi/{id}', [SyllabusController::class, 'destroy'])->name('faculty.syllabi.destroy');

    // ---------- ✅ ILO CRUD + Sortable ----------
    Route::put('/faculty/syllabi/{syllabus}/ilos', [SyllabusIloController::class, 'update'])->name('faculty.syllabi.ilos.update');
    Route::post('/faculty/syllabi/ilos/store', [SyllabusIloController::class, 'store'])->name('faculty.syllabi.ilos.store');
    Route::put('/faculty/syllabi/ilos/{syllabus}/{ilo}', [SyllabusIloController::class, 'inlineUpdate'])->name('faculty.syllabi.ilos.inline');
    Route::delete('/faculty/syllabi/ilos/{id}', [SyllabusIloController::class, 'destroy'])->name('faculty.syllabi.ilos.destroy');
    Route::post('/faculty/syllabi/reorder/ilo', [SyllabusIloController::class, 'reorder'])->name('faculty.syllabi.ilos.reorder');

    // ---------- IGA (Institutional Graduate Attributes) — managed by dedicated controller ----------
    Route::put('/faculty/syllabi/{syllabus}/igas', [\App\Http\Controllers\Faculty\SyllabusIgaController::class, 'update'])->name('faculty.syllabi.iga.update');
    Route::post('/faculty/syllabi/igas/reorder', [\App\Http\Controllers\Faculty\SyllabusIgaController::class, 'reorder'])->name('faculty.syllabi.iga.reorder');
    Route::delete('/faculty/syllabi/igas/{id}', [\App\Http\Controllers\Faculty\SyllabusIgaController::class, 'destroy'])->name('faculty.syllabi.iga.destroy');

    // ---------- CDIO (Conceive–Design–Implement–Operate) — per-syllabus CDIO CRUD + Sortable ----------
    Route::put('/faculty/syllabi/{syllabus}/cdios', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'update'])->name('faculty.syllabi.cdios.update');
    Route::post('/faculty/syllabi/{syllabus}/cdios/reorder', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'reorder'])->name('faculty.syllabi.cdios.reorder');
    Route::post('/faculty/syllabi/cdios', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'store'])->name('faculty.syllabi.cdios.store');
    Route::put('/faculty/syllabi/{syllabus}/cdios/{cdio}', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'inlineUpdate'])->name('faculty.syllabi.cdios.inline');
    Route::delete('/faculty/syllabi/cdios/{id}', [\App\Http\Controllers\Faculty\SyllabusCdioController::class, 'destroy'])->name('faculty.syllabi.cdios.destroy');

    // ---------- ✅ SO CRUD + Sortable ----------
    Route::put('/faculty/syllabi/{syllabus}/sos', [SyllabusSoController::class, 'update'])->name('faculty.syllabi.sos.update');
    Route::post('/faculty/syllabi/{syllabus}/sos/reorder', [SyllabusSoController::class, 'reorder'])->name('faculty.syllabi.sos.reorder');
    Route::delete('/faculty/syllabi/sos/{id}', [SyllabusSoController::class, 'destroy'])->name('faculty.syllabi.sos.destroy');

    // ---------- ✅ SDG Mapping ----------
    Route::post('/faculty/syllabi/{syllabus}/sdgs', [SyllabusSdgController::class, 'attach'])->name('faculty.syllabi.sdgs.attach');
    // Bulk save (order + descriptions)
    Route::put('/faculty/syllabi/{syllabus}/sdgs', [SyllabusSdgController::class, 'bulkUpdate'])->name('faculty.syllabi.sdgs.save');
    // Reorder positions only
    Route::post('/faculty/syllabi/{syllabus}/sdgs/reorder', [SyllabusSdgController::class, 'reorder'])->name('faculty.syllabi.sdgs.reorder');
    Route::put('/faculty/syllabi/{syllabus}/sdgs/update/{pivot}', [SyllabusSdgController::class, 'update'])->name('faculty.syllabi.sdgs.update');
    Route::delete('/faculty/syllabi/{syllabus}/sdgs/{sdg}', [SyllabusSdgController::class, 'detach'])->name('faculty.syllabi.sdgs.detach');
    // Delete per-syllabus SDG entry by its entry id
    Route::delete('/faculty/syllabi/{syllabus}/sdgs/entry/{id}', [SyllabusSdgController::class, 'destroyEntry'])->name('faculty.syllabi.sdgs.destroy_entry');

    // ---------- 📄 Textbook Upload / Delete / List / Update ----------
    Route::post('/faculty/syllabi/{syllabus}/textbook', [SyllabusTextbookController::class, 'store'])->name('faculty.syllabi.textbook.upload');
    Route::delete('/faculty/syllabi/textbook/{textbook}', [SyllabusTextbookController::class, 'destroy'])->name('faculty.syllabi.textbook.delete');
    Route::put('/faculty/syllabi/textbook/{textbook}', [SyllabusTextbookController::class, 'update'])->name('faculty.syllabi.textbook.update');
    Route::get('/faculty/syllabi/{syllabus}/textbook/list', [SyllabusTextbookController::class, 'list'])->name('faculty.syllabi.textbook.list');

    // ---------- TLA CRUD + Mapping ----------
    Route::post('/faculty/syllabi/{id}/tla', [SyllabusTLAController::class, 'update'])->name('faculty.syllabi.tla.update');
    Route::post('/faculty/syllabi/{id}/tla/append', [SyllabusTLAController::class, 'append'])->name('faculty.syllabi.tla.append');
    Route::delete('/faculty/syllabi/tla/{id}', [SyllabusTLAController::class, 'destroy'])->name('faculty.syllabi.tla.delete');
    Route::match(['get', 'post'], '/faculty/syllabi/tla/{id}/sync-ilo', [SyllabusTLAController::class, 'syncIlo'])->name('faculty.syllabi.tla.sync-ilo');
    Route::match(['get', 'post'], '/faculty/syllabi/tla/{id}/sync-so', [SyllabusTLAController::class, 'syncSo'])->name('faculty.syllabi.tla.sync-so');

    // ---------- Export Routes ----------
    Route::get('/faculty/syllabi/{id}/export/pdf', [SyllabusController::class, 'exportPdf'])->name('faculty.syllabi.export.pdf');
    Route::get('/faculty/syllabi/{id}/export/word', [SyllabusController::class, 'exportWord'])->name('faculty.syllabi.export.word');

    Route::post('/faculty/syllabi/{syllabus}/generate-tla', [SyllabusTLAController::class, 'generateWithAI'])
     ->name('faculty.syllabi.tla.generate');

    // Persist Assessment Tasks payload for a syllabus
    Route::post('/faculty/syllabi/{syllabus}/assessment-tasks', [SyllabusController::class, 'saveAssessmentTasks'])->name('faculty.syllabi.assessment_tasks.save');
    // Persist Assessment Mappings payload for a syllabus
    Route::post('/faculty/syllabi/{syllabus}/assessment-mappings', [SyllabusController::class, 'saveAssessmentMappings'])->name('faculty.syllabi.assessment_mappings.save');

    // Persist ILO -> SO -> CPA mapping payload for a syllabus
    Route::post('/faculty/syllabi/{syllabus}/ilo-so-cpa', [SyllabusController::class, 'saveIloSoCpa'])->name('faculty.syllabi.ilo_so_cpa.save');
    // Persist ILO -> IGA mapping payload for a syllabus
    Route::post('/faculty/syllabi/{syllabus}/ilo-iga', [SyllabusController::class, 'saveIloIga'])->name('faculty.syllabi.ilo_iga.save');
    // Persist ILO -> CDIO -> SDG mapping payload for a syllabus
    Route::post('/faculty/syllabi/{syllabus}/ilo-cdio-sdg', [SyllabusController::class, 'saveIloCdioSdg'])->name('faculty.syllabi.ilo_cdio_sdg.save');

    // ---------- Faculty Manage Accounts ----------
    Route::get('/faculty/manage-accounts', [ManageFacultyAccountController::class, 'index'])->name('faculty.manage-accounts.index');
    Route::post('/faculty/manage-accounts/{id}/approve', [ManageFacultyAccountController::class, 'approve'])->name('faculty.manage-accounts.approve');
    Route::post('/faculty/manage-accounts/{id}/reject', [ManageFacultyAccountController::class, 'reject'])->name('faculty.manage-accounts.reject');

});

// ---------- Logout ----------
Route::post('/faculty/logout', function () {
    Auth::guard('faculty')->logout();
    return redirect()->route('faculty.login.form');
})->name('faculty.logout');
