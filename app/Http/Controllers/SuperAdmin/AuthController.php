<?php
// -----------------------------------------------------------------------------
// File: app/Http/Controllers/SuperAdmin/AuthController.php
// Description: Handles Super Admin login and logout using .env credentials – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Initial creation – handles login and logout using session-based auth.
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\SuperAdmin;

class AuthController extends Controller
{
    // START: Super Admin Login

    /**
     * Authenticate Super Admin using static credentials in .env.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        // Prefer DB-stored superadmin credentials if present
        $dbOk = false;
        try {
            $admin = SuperAdmin::where('username', $request->username)->first();
            if ($admin && Hash::check($request->password, $admin->password)) {
                $dbOk = true;
            }
        } catch (\Throwable $e) {
            // ignore and fallback to env
        }

        if ($dbOk || (
            $request->username === env('SUPERADMIN_USERNAME') &&
            $request->password === env('SUPERADMIN_PASSWORD')
        )) {
            // Regenerate session ID but preserve other guards' data (admin/faculty)
            $request->session()->regenerate();
            Session::put('is_superadmin', true);

            // Handle remember me: set a long-lived cookie to auto-restore session flag
            if ($request->boolean('remember')) {
                // 30 days expiry
                $minutes = 60 * 24 * 30;
                cookie()->queue(cookie('superadmin_remember', '1', $minutes, null, null, true, true, false, 'Lax'));
            }
            return redirect()->intended(route('superadmin.dashboard'));
        }

        return redirect()->route('superadmin.login.form')
            ->with('error', 'Invalid username or password.');
    }

    // END: Super Admin Login

    // START: Super Admin Logout

    /**
     * Logout Super Admin and destroy session.
     */
    public function logout(Request $request)
    {
        // Only remove superadmin flag; do not invalidate entire session so
        // admin/faculty sessions remain intact when used concurrently
        Session::forget('is_superadmin');
        // Clear remember cookie
        cookie()->queue(cookie('superadmin_remember', null, -60));
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login.form');
    }

    // END: Super Admin Logout
}
