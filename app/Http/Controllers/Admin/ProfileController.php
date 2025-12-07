<?php
// -----------------------------------------------------------------------------
// File: app/Http/Controllers/Admin/ProfileController.php
// Description: Admin Manage Profile page (view + update)
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /** Show the manage profile page */
    public function edit()
    {
        $user = Auth::user();
        return view('admin.manage-profile', compact('user'));
    }

    /** Update admin profile (name, email, optional password) */
    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->save();

        return redirect()->back()->with('status', 'Profile updated successfully.');
    }
}
