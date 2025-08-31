<?php
// File: app/Http/Middleware/AdminAuth.php
// Description: Middleware to verify admin access and profile completeness

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle($request, Closure $next)
    {
        // Ensure the admin guard is used for this request
        Auth::shouldUse('admin');

        $user = Auth::guard('admin')->user();

        // Check if user is logged in and is an admin
        if (!Auth::guard('admin')->check() || !$user || $user->role !== 'admin') {
            return redirect()->route('admin.login.form')->with('error', 'Unauthorized access.');
        }

        // Redirect to complete profile if essential fields are missing
        if (is_null($user->designation) || is_null($user->employee_code)) {
            if (!$request->is('admin/complete-profile')) {
                return redirect()->route('admin.complete-profile')->with('info', 'Please complete your profile before proceeding.');
            }
        }

        return $next($request);
    }
}
