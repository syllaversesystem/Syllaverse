<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/AuthController.php
// * Description: Handles Google login for Admin with approval flow + pending redirect – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Updated: allow pending admins to log in and redirect to Complete Profile instead of blocking.
// [2025-08-08] Change: if pending AND profile already completed, do NOT login; show "pending approval" alert.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->redirectUrl(env('GOOGLE_REDIRECT_URI_ADMIN'))
            ->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl(env('GOOGLE_REDIRECT_URI_ADMIN'))
                ->user();

            $email = $googleUser->getEmail();

            // Restrict login to BSU GSuite domain
            if (!str_ends_with($email, '@g.batstate-u.edu.ph')) {
                return redirect()->route('admin.login.form')->withErrors([
                    'email' => 'Only BSU GSuite accounts are allowed.',
                ]);
            }

            // Find or create Admin user
            $user = User::where('email', $email)->first();

            if ($user) {
                if ($user->role !== 'admin') {
                    return redirect()->route('admin.login.form')->withErrors([
                        'role' => 'This account is already registered as a different role.',
                    ]);
                }
            } else {
                // First-time signup → create pending admin (no HR fields yet)
                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $email,
                    'google_id' => $googleUser->getId(),
                    'role'      => 'admin',
                    'status'    => 'pending',
                ]);
            }

            // Hard stop if explicitly rejected
            if ($user->status === 'rejected') {
                return redirect()->route('admin.login.form')->withErrors([
                    'rejected' => 'Your account has been rejected. Please contact the Super Admin.',
                ]);
            }

            // If active → normal login
            if ($user->status === 'active') {
                Auth::guard('admin')->login($user);
                return redirect()->route('admin.dashboard');
            }

            // Status is PENDING here:
            $needsProfile = empty($user->designation) || empty($user->employee_code);

            if ($needsProfile) {
                // First-time (or incomplete) profile → allow login and send to Complete Profile
                Auth::guard('admin')->login($user);
                return redirect()
                    ->route('admin.complete-profile')
                    ->with('info', 'Please complete your profile and submit your chair role request for Superadmin review.');
            }

            // Already completed profile but still pending → DO NOT LOG IN; show pending alert
// Pending + needs profile (inside handleGoogleCallback)
return redirect()
  ->route('admin.complete-profile')
  ->with('info', 'Please complete your profile and submit your chair role request for Superadmin review.');


        } catch (\Exception $e) {
            return redirect()->route('admin.login.form')->withErrors([
                'login' => 'Google login failed. Please try again.',
            ]);
        }
    }
}
