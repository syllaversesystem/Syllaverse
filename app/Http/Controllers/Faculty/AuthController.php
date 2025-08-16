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

class AuthController extends Controller
{
    /**
     * Redirect the Faculty to Google's OAuth page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirectUrl(env('GOOGLE_REDIRECT_URI_FACULTY'))
            ->redirect();
    }

    /**
     * Handle the callback from Google OAuth for Faculty login.
     */
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl(env('GOOGLE_REDIRECT_URI_FACULTY'))
            ->user();

        $email = $googleUser->getEmail();

        $user = User::where('email', $email)->first();

        if ($user) {
            // Check if the account is a faculty
            if ($user->role !== 'faculty') {
                return redirect()->route('faculty.login.form')
                    ->with('error', 'This account is already registered as a different role.');
            }

            // Update google_id if missing
            if (empty($user->google_id)) {
                $user->google_id = $googleUser->getId();
                $user->save();
            }
        } else {
            // New faculty registration
            $user = User::create([
                'name'       => $googleUser->getName(),
                'email'      => $email,
                'google_id'  => $googleUser->getId(),
                'role'       => 'faculty',
                'status'     => 'active', // Will be set to pending after profile completion
            ]);
        }

        Auth::login($user);

        // Redirect logic
        if ($user->status === 'pending') {
            // Needs profile completion
            return redirect()->route('faculty.complete-profile.show');
        }

        // Approved & complete
        return redirect()->route('faculty.dashboard');
    }
}
