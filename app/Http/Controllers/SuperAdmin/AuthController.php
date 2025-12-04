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
        ]);

        if (
            $request->username === env('SUPERADMIN_USERNAME') &&
            $request->password === env('SUPERADMIN_PASSWORD')
        ) {
            // Regenerate session ID but preserve other guards' data (admin/faculty)
            $request->session()->regenerate();
            Session::put('is_superadmin', true);
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
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login.form');
    }

    // END: Super Admin Logout
}
