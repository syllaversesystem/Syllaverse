<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Models\SuperAdmin;

class ProfileController extends Controller
{
    /** Show the manage profile page */
    public function edit()
    {
        $user = SuperAdmin::first();
        return view('superadmin.manage-profile.index', compact('user'));
    }


    /** Update superadmin profile (username, optional password); email change via verification link */
    public function update(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user = SuperAdmin::first();
        if (!$user) {
            return back()->withErrors(['profile' => 'No Superadmin record found.']);
        }
        $user->username = $request->input('username');
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->save();

        // If a new email is provided, send verification link instead of direct save
        if ($request->filled('email')) {
            try {
                // Reuse existing email change flow from AuthController
                // Generate signed verification URL to confirm new email
                $new = $request->input('email');
                $verifyUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('superadmin.email.verify', now()->addMinutes(60), [
                    'email' => $new,
                ]);
                \Illuminate\Support\Facades\Mail::send('emails.superadmin-verify-email', [
                    'verifyUrl' => $verifyUrl,
                    'newEmail' => $new,
                    'appName' => config('app.name', 'Syllaverse'),
                ], function ($m) use ($new) {
                    $m->to($new)->subject('Syllaverse • Verify Admin Email');
                });
                return redirect()->back()->with('status', 'Profile updated. Verification link sent to the new email.');
            } catch (\Throwable $e) {
                return redirect()->back()->withErrors(['email' => 'Failed to send verification email.']);
            }
        }

        return redirect()->back()->with('status', 'Superadmin profile updated.');
    }
}
