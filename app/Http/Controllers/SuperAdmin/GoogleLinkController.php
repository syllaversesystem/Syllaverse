<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Session;

class GoogleLinkController extends Controller
{
    public function link(Request $request)
    {
        // Initiate Google OAuth; request basic scopes to get email
        $redirect = config('services.google.redirect_superadmin');
        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl($redirect)
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $redirect = config('services.google.redirect_superadmin');
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl($redirect)
                ->user();
            $email = $googleUser->getEmail();
            if (!$email) {
                return redirect()->route('superadmin.manage-profile')->withErrors(['email' => 'Could not retrieve Google email.']);
            }

            $user = SuperAdmin::first();
            if (!$user) {
                return redirect()->route('superadmin.manage-profile')->withErrors(['profile' => 'No Superadmin record found.']);
            }

            $user->email = $email;
            $user->email_verified_at = now();
            $user->save();

            // Flash success; alert overlay will pick this up
            Session::flash('success', 'Google account linked successfully.');
            return redirect()->route('superadmin.manage-profile');
        } catch (\Throwable $e) {
            return redirect()->route('superadmin.manage-profile')->withErrors(['google' => 'Failed to link Google account.']);
        }
    }
}
