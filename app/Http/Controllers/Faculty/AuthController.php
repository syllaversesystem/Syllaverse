<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/AuthController.php
// * Description: Handles Google Login for Faculty (Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-16] Added google_id capture, skip profile completion if already complete and approved.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * Redirect the Faculty to Google's OAuth page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->redirectUrl(env('GOOGLE_REDIRECT_URI_FACULTY'))
            ->redirect();
    }

    /**
     * Handle the callback from Google OAuth for Faculty login.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl(env('GOOGLE_REDIRECT_URI_FACULTY'))
                ->user();

            $email = $googleUser->getEmail();

            // Restrict login to BSU GSuite domain (same policy as Admin)
            if (!str_ends_with($email, '@g.batstate-u.edu.ph')) {
                return redirect()->route('faculty.login.form')->withErrors([
                    'email' => 'Only BSU GSuite accounts are allowed.',
                ]);
            }

            $user = User::where('email', $email)->first();

            // Extract employee code from email local part (before @)
            $localPart = explode('@', $email)[0] ?? '';
            // Normalize: trim and keep original pattern (e.g., 22-70787)
            $extractedCode = trim($localPart);

            if ($user) {
                // Role guard
                if ($user->role !== 'faculty') {
                    return redirect()->route('faculty.login.form')->withErrors([
                        'role' => 'This account is already registered as a different role.',
                    ]);
                }

                // Backfill google_id
                if (empty($user->google_id)) {
                    $user->google_id = $googleUser->getId();
                }
                // Backfill employee_code if missing
                if (empty($user->employee_code) && $extractedCode !== '') {
                    $user->employee_code = $extractedCode;
                }
                $user->save();
            } else {
                // First-time faculty user
                $user = User::create([
                    'name'       => $googleUser->getName(),
                    'email'      => $email,
                    'google_id'  => $googleUser->getId(),
                    'role'       => 'faculty',
                    'status'     => 'pending', // stay pending until profile complete and approved
                    'employee_code' => $extractedCode ?: null,
                ]);
            }

            // Hard stop if explicitly rejected
            if ($user->status === 'rejected') {
                return redirect()->route('faculty.login.form')->withErrors([
                    'rejected' => 'Your account has been rejected. Please contact the administrator.',
                ]);
            }

            if ($user->status === 'active') {
                Auth::guard('faculty')->login($user);
                // Redirect approved faculty to Syllabi index instead of dashboard
                return redirect()->route('faculty.syllabi.index');
            }

            // Pending: if profile is incomplete, allow login to complete it
            $needsProfile = empty($user->designation) || empty($user->employee_code);
            if ($needsProfile) {
                Auth::guard('faculty')->login($user);
                return redirect()
                    ->route('faculty.complete-profile')
                    ->with('info', 'Please complete your profile. Your account will be reviewed and approved.');
            }

            // Pending but profile already complete -> do not login; inform user awaiting approval
            return redirect()->route('faculty.login.form')->withErrors([
                'pending' => 'Your account is pending approval. You will be notified once approved.',
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('faculty.login.form')->withErrors([
                'login' => 'Google login failed. Please try again.',
            ]);
        }
    }
}
