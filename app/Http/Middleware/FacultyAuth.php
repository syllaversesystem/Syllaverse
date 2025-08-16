<?php
// File: app/Http/Middleware/FacultyAuth.php
// Description: Middleware to protect faculty-only routes and ensure profile completion and approval (appointments-based)

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class FacultyAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Must be logged in and faculty role
        if (!Auth::check() || Auth::user()->role !== 'faculty') {
            return redirect()->route('faculty.login.form')
                ->with('error', 'Access denied. Please log in as faculty.');
        }

        $user = Auth::user();

        // 1️⃣ Check if designation and employee_code are filled
        if (empty($user->designation) || empty($user->employee_code)) {
            return redirect()->route('faculty.complete-profile')
                ->with('error', 'Please complete your profile before accessing the dashboard.');
        }

        // 2️⃣ Check if user has an active faculty appointment
        $hasActiveAppointment = Appointment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('role', Appointment::ROLE_FACULTY)
                  ->orWhere('role', Appointment::ROLE_PROG)
                  ->orWhere('role', Appointment::ROLE_DEPT);
            })
            ->exists();

        if (!$hasActiveAppointment) {
            return redirect()->route('faculty.complete-profile')
                ->with('error', 'You are not yet linked to any department/program. Please contact your Program Chair.');
        }

        // 3️⃣ Check if faculty is approved
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('faculty.login.form')
                ->with('error', 'Your account is pending approval by your Program Chair.');
        }

        return $next($request);
    }
}
