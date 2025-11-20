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
use App\Http\Controllers\Faculty\Syllabus\SyllabusController;
use App\Http\Controllers\Faculty\Syllabus\SyllabusTextbookController;
use App\Http\Controllers\Faculty\Syllabus\SyllabusTLAController;
use App\Http\Controllers\Faculty\Syllabus\SyllabusIloController;
use App\Http\Controllers\Faculty\Syllabus\SyllabusSoController;
use App\Http\Controllers\Faculty\Syllabus\SyllabusSdgController;
use App\Http\Controllers\Faculty\SdgController;
use App\Http\Controllers\Faculty\IgaController;
use App\Http\Controllers\Faculty\CdioController;
use App\Http\Controllers\Faculty\DepartmentsController;
use App\Http\Controllers\Faculty\ProgramController;
use App\Http\Controllers\Faculty\CourseController;
use App\Http\Controllers\Faculty\MasterDataController;
use App\Http\Controllers\Faculty\StudentOutcomeController;
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

    // ---------- Master Data (SO, ILO, SDG, IGA, CDIO) ----------
    // SDG Master Data
    Route::get('/faculty/master-data/sdg/filter', [SdgController::class, 'filterByDepartment']);
    Route::post('/faculty/master-data/sdg', [SdgController::class, 'store']);
    Route::put('/faculty/master-data/sdg/{id}', [SdgController::class, 'update']);
    Route::delete('/faculty/master-data/sdg/{id}', [SdgController::class, 'destroy']);
    // IGA Master Data
    Route::get('/faculty/master-data/iga/filter', [IgaController::class, 'filter']);
    Route::post('/faculty/master-data/iga', [IgaController::class, 'store']);
    Route::put('/faculty/master-data/iga/{id}', [IgaController::class, 'update']);
    Route::delete('/faculty/master-data/iga/{id}', [IgaController::class, 'destroy']);
    // ILO master data
    Route::get('/faculty/master-data/ilo/filter', [\App\Http\Controllers\Faculty\IloController::class, 'filter']);
    Route::post('/faculty/master-data/ilo', [\App\Http\Controllers\Faculty\IloController::class, 'store']);
    Route::put('/faculty/master-data/ilo/{id}', [\App\Http\Controllers\Faculty\IloController::class, 'update']);
    Route::delete('/faculty/master-data/ilo/{id}', [\App\Http\Controllers\Faculty\IloController::class, 'destroy']);
    Route::post('/faculty/master-data/ilo/reorder', [\App\Http\Controllers\Faculty\IloController::class, 'reorder']);

    // CDIO Master Data
    Route::get('/faculty/master-data/cdio/filter', [CdioController::class, 'filter'])->name('cdio.filter');
    Route::post('/faculty/master-data/cdio', [CdioController::class, 'store'])->name('cdio.store');
    Route::put('/faculty/master-data/cdio/{id}', [CdioController::class, 'update'])->name('cdio.update');
    Route::delete('/faculty/master-data/cdio/{id}', [CdioController::class, 'destroy'])->name('cdio.destroy');
    Route::get('/faculty/master-data', [MasterDataController::class, 'index'])->name('faculty.master-data.index');
    // SO (Student Outcomes) Master Data
    Route::get('/faculty/master-data/so/filter', [StudentOutcomeController::class, 'filterByDepartment'])->name('faculty.master-data.so.filter');
    Route::post('/faculty/master-data/so', [StudentOutcomeController::class, 'store'])->name('faculty.master-data.so.store');
    Route::put('/faculty/master-data/so/{id}', [StudentOutcomeController::class, 'update'])->name('faculty.master-data.so.update');
    Route::delete('/faculty/master-data/so/{id}', [StudentOutcomeController::class, 'destroy'])->name('faculty.master-data.so.destroy');



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
    Route::get('/faculty/programs/search-removed', [ProgramController::class, 'searchRemoved'])->name('faculty.programs.search-removed');
    Route::get('/faculty/programs/filter', [ProgramController::class, 'filterByDepartment'])->name('faculty.programs.filter');

    // ---------- Courses Management ----------
    Route::get('/faculty/courses', [CourseController::class, 'index'])->name('faculty.courses.index');
    Route::post('/faculty/courses', [CourseController::class, 'store'])->name('faculty.courses.store');
    Route::put('/faculty/courses/{course}', [CourseController::class, 'update'])->name('faculty.courses.update');
    Route::delete('/faculty/courses/{course}', [CourseController::class, 'destroy'])->name('faculty.courses.destroy');
    Route::get('/faculty/courses/search-deleted', [CourseController::class, 'searchDeleted'])->name('faculty.courses.search-deleted');
    Route::get('/faculty/courses/filter', [CourseController::class, 'filterByDepartment'])->name('faculty.courses.filter');

    // ---------- Syllabus Routes ----------
    Route::get('/faculty/syllabi', [SyllabusController::class, 'index'])->name('faculty.syllabi.index');
    Route::get('/faculty/syllabi/create', [SyllabusController::class, 'create'])->name('faculty.syllabi.create');
    Route::post('/faculty/syllabi', [SyllabusController::class, 'store'])->name('faculty.syllabi.store');
    Route::get('/faculty/syllabi/proceed', [SyllabusController::class, 'proceed'])->name('faculty.syllabi.proceed');
    Route::get('/faculty/syllabi/{id}', [SyllabusController::class, 'show'])->name('faculty.syllabi.show');
    Route::put('/faculty/syllabi/{id}', [SyllabusController::class, 'update'])->name('faculty.syllabi.update');
    Route::delete('/faculty/syllabi/{id}', [SyllabusController::class, 'destroy'])->name('faculty.syllabi.destroy');
    Route::get('/faculty/syllabi/{id}/predefined-policies', [\App\Http\Controllers\Faculty\Syllabus\SyllabusCoursePolicyController::class, 'getPredefinedPolicies'])->name('faculty.syllabi.predefined-policies');
    
    // (Removed live save endpoints: mission-vision, course-info, tlas, criteria, ilo-save)
    // ---------- ILO CRUD & Batch Operations (replacing deprecated IloSaveController) ----------
    // Batch upsert (create/update/delete based on payload) of syllabus ILOs
    Route::put('/faculty/syllabi/{syllabus}/ilos', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIloController::class, 'update'])->name('faculty.syllabi.ilos.update');
    // Create a single new ILO (appends at end; code auto-generated)
    Route::post('/faculty/syllabi/{syllabus}/ilos', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIloController::class, 'store'])->name('faculty.syllabi.ilos.store');
    // Inline update description of a single ILO
    Route::put('/faculty/syllabi/{syllabus}/ilos/{ilo}/inline', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIloController::class, 'inlineUpdate'])->name('faculty.syllabi.ilos.inline');
    // Delete a single ILO
    Route::delete('/faculty/syllabi/ilos/{ilo}', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIloController::class, 'destroy'])->name('faculty.syllabi.ilos.destroy');
    // Reorder + recode ILOs (positions array)
    Route::post('/faculty/syllabi/{syllabus}/ilos/reorder', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIloController::class, 'reorder'])->name('faculty.syllabi.ilos.reorder');
    Route::post('/faculty/syllabi/{syllabus}/load-predefined-ilos', [SyllabusIloController::class, 'loadPredefinedIlos'])->name('faculty.syllabi.ilos.load-predefined');

    // ---------- Assessment Tasks & Distribution Map ----------
    Route::get('/faculty/syllabi/{syllabus}/assessment-tasks', [\App\Http\Controllers\Faculty\Syllabus\SyllabusAssessmentTasksController::class, 'index'])->name('faculty.syllabi.assessment-tasks.index');
    Route::post('/faculty/syllabi/{syllabus}/assessment-tasks', [\App\Http\Controllers\Faculty\Syllabus\SyllabusAssessmentTasksController::class, 'store'])->name('faculty.syllabi.assessment-tasks.store');

    // ---------- Assessment Mappings (Weekly Distribution) ----------
    Route::get('/faculty/syllabi/{id}/assessment-mappings', [\App\Http\Controllers\Faculty\Syllabus\SyllabusAssessmentMappingController::class, 'index'])->name('faculty.syllabi.assessment-mappings.index');
    Route::post('/faculty/syllabi/{id}/assessment-mappings', [\App\Http\Controllers\Faculty\Syllabus\SyllabusAssessmentMappingController::class, 'update'])->name('faculty.syllabi.assessment-mappings.update');

    // ---------- IGA (Institutional Graduate Attributes) — managed by dedicated controller ----------
    Route::put('/faculty/syllabi/{syllabus}/igas', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIgaController::class, 'update'])->name('faculty.syllabi.iga.update');
    Route::post('/faculty/syllabi/{syllabus}/load-predefined-igas', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIgaController::class, 'loadPredefinedIgas'])->name('faculty.syllabi.igas.load-predefined');
    Route::post('/faculty/syllabi/igas/reorder', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIgaController::class, 'reorder'])->name('faculty.syllabi.iga.reorder');
    Route::delete('/faculty/syllabi/igas/{id}', [\App\Http\Controllers\Faculty\Syllabus\SyllabusIgaController::class, 'destroy'])->name('faculty.syllabi.iga.destroy');

    // ---------- CDIO (Conceive–Design–Implement–Operate) — per-syllabus CDIO CRUD + Sortable ----------
    Route::put('/faculty/syllabi/{syllabus}/cdios', [\App\Http\Controllers\Faculty\Syllabus\SyllabusCdioController::class, 'update'])->name('faculty.syllabi.cdios.update');
    Route::post('/faculty/syllabi/{syllabus}/cdios/reorder', [\App\Http\Controllers\Faculty\Syllabus\SyllabusCdioController::class, 'reorder'])->name('faculty.syllabi.cdios.reorder');
    Route::post('/faculty/syllabi/{syllabus}/load-predefined-cdios', [\App\Http\Controllers\Faculty\Syllabus\SyllabusCdioController::class, 'loadPredefinedCdios'])->name('faculty.syllabi.cdios.load-predefined');
    Route::post('/faculty/syllabi/cdios', [\App\Http\Controllers\Faculty\Syllabus\SyllabusCdioController::class, 'store'])->name('faculty.syllabi.cdios.store');
    Route::put('/faculty/syllabi/{syllabus}/cdios/{cdio}', [\App\Http\Controllers\Faculty\Syllabus\SyllabusCdioController::class, 'inlineUpdate'])->name('faculty.syllabi.cdios.inline');
    Route::delete('/faculty/syllabi/cdios/{id}', [\App\Http\Controllers\Faculty\Syllabus\SyllabusCdioController::class, 'destroy'])->name('faculty.syllabi.cdios.destroy');

    // ---------- ✅ SO CRUD + Sortable ----------
    Route::put('/faculty/syllabi/{syllabus}/sos', [SyllabusSoController::class, 'update'])->name('faculty.syllabi.sos.update');
    Route::post('/faculty/syllabi/{syllabus}/sos/reorder', [SyllabusSoController::class, 'reorder'])->name('faculty.syllabi.sos.reorder');
    Route::post('/faculty/syllabi/{syllabus}/load-predefined-sos', [SyllabusSoController::class, 'loadPredefinedSos'])->name('faculty.syllabi.sos.load-predefined');
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

    // (Removed bulk mapping save endpoints: assessment tasks/mappings, ilo-so-cpa, ilo-iga, ilo-cdio-sdg)



});

// ---------- Logout ----------
Route::post('/faculty/logout', function () {
    Auth::guard('faculty')->logout();
    return redirect()->route('faculty.login.form');
})->name('faculty.logout');
