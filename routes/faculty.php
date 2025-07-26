<?php

// ------------------------------------------------
// File: routes/faculty.php
// Description: Faculty-specific routes for login, dashboard, syllabus CRUD, file upload and export (Syllaverse)
// ------------------------------------------------

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Faculty\AuthController as FacultyAuthController;
use App\Http\Controllers\Faculty\ProfileController;
use App\Http\Controllers\Faculty\SyllabusController;
use App\Http\Controllers\Faculty\SyllabusTextbookController;
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

    // ---------- Textbook Upload (AJAX) ----------
    Route::post('/faculty/syllabi/{id}/textbook', [SyllabusTextbookController::class, 'store'])->name('faculty.syllabi.textbook.upload');

    // ---------- Export Routes ----------
    Route::get('/faculty/syllabi/{id}/export/pdf', [SyllabusController::class, 'exportPdf'])->name('faculty.syllabi.export.pdf');
    Route::get('/faculty/syllabi/{id}/export/word', [SyllabusController::class, 'exportWord'])->name('faculty.syllabi.export.word');
});

// ---------- Logout ----------
Route::post('/faculty/logout', function () {
    Auth::logout();
    return redirect()->route('faculty.login.form')->with('success', 'Logged out successfully.');
})->name('faculty.logout');
