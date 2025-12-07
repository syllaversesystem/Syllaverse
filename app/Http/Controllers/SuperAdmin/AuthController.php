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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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

        // Authenticate exclusively against DB-stored superadmin credentials
        $admin = SuperAdmin::where('username', $request->username)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
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

    /**
     * Send a reset request email with a signed link.
     */
    public function forgotRequest(Request $request)
    {
        try {
            $signedUrl = URL::temporarySignedRoute('superadmin.reset.form', now()->addMinutes(30));
            $admin = SuperAdmin::first();
            if (!$admin || empty($admin->email) || !$admin->email_verified_at) {
                return back()->withErrors(['reset' => 'Superadmin email not set or not verified.']);
            }
            $to = $admin->email;
            Mail::send('emails.superadmin-reset', [
                'signedUrl' => $signedUrl,
                'appName' => config('app.name', 'Syllaverse'),
            ], function ($m) use ($to) {
                $m->to($to)->subject('Syllaverse • Super Admin Password Reset');
            });
            return back()->with('status', 'Reset link sent. Please check your email.');
        } catch (\Throwable $e) {
            return back()->withErrors(['reset' => 'Failed to send reset link.']);
        }
    }

    /** Show reset form (from email link). */
    public function resetForm(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }
        return view('auth.superadmin-reset');
    }

    /** Process reset update after email link. */
    public function resetUpdate(Request $request)
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);
        try {
            $admin = SuperAdmin::first();
            if (!$admin) {
                return back()->withErrors(['reset' => 'No Super Admin record found.']);
            }
            $admin->password = Hash::make($request->new_password);
            $admin->save();
            return redirect()->route('superadmin.login.form')->with('status', 'Password updated. Please login.');
        } catch (\Throwable $e) {
            return back()->withErrors(['reset' => 'Failed to update password.']);
        }
    }

    /** Request change of superadmin email: sends verification link to new address */
    public function requestEmailChange(Request $request)
    {
        $request->validate(['new_email' => ['required', 'email']]);
        $new = $request->input('new_email');
        $verifyUrl = URL::temporarySignedRoute('superadmin.email.verify', now()->addMinutes(60), [
            'email' => $new,
        ]);
        try {
            Mail::send('emails.superadmin-verify-email', [
                'verifyUrl' => $verifyUrl,
                'newEmail' => $new,
                'appName' => config('app.name', 'Syllaverse'),
            ], function ($m) use ($new) {
                $m->to($new)->subject('Syllaverse • Verify Admin Email');
            });
            return back()->with('status', 'Verification link sent to the new email.');
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Failed to send verification email.']);
        }
    }

    /** Verify email change and persist to DB */
    public function verifyEmailChange(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }
        $email = $request->query('email');
        $admin = SuperAdmin::first();
        if (!$admin) {
            abort(404);
        }
        $admin->email = $email;
        $admin->email_verified_at = now();
        $admin->save();
        return redirect()->route('superadmin.login.form')->with('status', 'Superadmin email verified and updated.');
    }
}
