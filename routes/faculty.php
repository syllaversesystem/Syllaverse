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
use App\Http\Middleware\FacultyAuth;

// ---------- Faculty Login Form View ----------
Route::get('/faculty/login', function () {
    return view('auth.faculty-login');
})->name('faculty.login.form');

// ---------- Google Login ----------
Route::get('/faculty/login/google', [FacultyAuthController::class, 'redirectToGoogle'])->name('faculty.google.login');
Route::get('/faculty/google/callback', [FacultyAuthController::class, 'handleGoogleCallback'])->name('faculty.google.callback');

// ---------- Faculty Profile Completion ----------
Route::middleware(['auth'])->group(function () {
    Route::get('/faculty/complete-profile', [ProfileController::class, 'showCompleteForm'])->name('faculty.complete-profile');
    Route::post('/faculty/complete-profile', [ProfileController::class, 'submitProfile'])->name('faculty.complete-profile.submit');
});

// ---------- Faculty-Only Protected Routes ----------
Route::middleware([FacultyAuth::class])->group(function () {
    Route::view('/faculty/dashboard', 'faculty.dashboard')->name('faculty.dashboard');

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

    // ---------- ✅ SO CRUD + Sortable ----------
    Route::put('/faculty/syllabi/{syllabus}/sos', [SyllabusSoController::class, 'update'])->name('faculty.syllabi.sos.update');
    Route::post('/faculty/syllabi/{syllabus}/sos/reorder', [SyllabusSoController::class, 'reorder'])->name('faculty.syllabi.sos.reorder');
    Route::delete('/faculty/syllabi/sos/{id}', [SyllabusSoController::class, 'destroy'])->name('faculty.syllabi.sos.destroy');

    // ---------- ✅ SDG Mapping ----------
    Route::post('/faculty/syllabi/{syllabus}/sdgs', [SyllabusSdgController::class, 'attach'])->name('faculty.syllabi.sdgs.attach');
    Route::put('/faculty/syllabi/{syllabus}/sdgs/update/{pivot}', [SyllabusSdgController::class, 'update'])->name('faculty.syllabi.sdgs.update');
    Route::delete('/faculty/syllabi/{syllabus}/sdgs/{sdg}', [SyllabusSdgController::class, 'detach'])->name('faculty.syllabi.sdgs.detach');

    // ---------- 📄 Textbook Upload / Delete / List ----------
    Route::post('/faculty/syllabi/{syllabus}/textbook', [SyllabusTextbookController::class, 'store'])->name('faculty.syllabi.textbook.upload');
    Route::delete('/faculty/syllabi/textbook/{textbook}', [SyllabusTextbookController::class, 'destroy'])->name('faculty.syllabi.textbook.delete');
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

});

// ---------- Logout ----------
Route::post('/faculty/logout', function () {
    Auth::logout();
    return redirect()->route('faculty.login.form')->with('success', 'Logged out successfully.');
})->name('faculty.logout');
