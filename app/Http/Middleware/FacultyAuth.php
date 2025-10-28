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
        // Ensure the faculty guard is used for this request
        Auth::shouldUse('faculty');

        // Must be logged in and faculty role (or admin)
        $user = Auth::guard('faculty')->user();
        if (!Auth::guard('faculty')->check() || !in_array($user?->role, ['faculty', 'admin'])) {
            return redirect()->route('faculty.login.form')
                ->with('error', 'Access denied. Please log in as faculty.');
        }

        // Admin users can skip all profile and appointment checks
        if ($user->role === 'admin') {
            return $next($request);
        }

        // 1️⃣ Check if designation and employee_code are filled (faculty only)
        if (empty($user->designation) || empty($user->employee_code)) {
            return redirect()->route('faculty.complete-profile')
                ->with('error', 'Please complete your profile before accessing the dashboard.');
        }

        // 2️⃣ Check if faculty is approved by superadmin (faculty only)
        if ($user->status !== 'active') {
            Auth::guard('faculty')->logout();
            return redirect()->route('faculty.login.form')
                ->with('error', 'Your account is pending approval by the Superadmin.');
        }

        // Note: Appointments are now managed through superadmin interface
        // Active faculty users can access the dashboard even without appointments initially

        return $next($request);
    }
}
