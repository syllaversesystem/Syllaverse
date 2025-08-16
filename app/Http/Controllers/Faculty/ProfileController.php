<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/ProfileController.php
// * Description: Handles faculty profile completion & links them via appointments – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-16] Merged updates – removed department_id from users table update,
//              connects faculty via appointments with FACULTY role/scope.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Department;

class ProfileController extends Controller
{
    /**
     * Show the complete profile form.
     */
    public function showCompleteForm()
    {
        $departments = Department::all();
        return view('faculty.complete-profile', compact('departments'));
    }

    /**
     * Handle the form submission.
     */
    public function submitProfile(Request $request)
    {
        $request->validate([
            'designation'    => 'required|string|max:255',
            'employee_code'  => 'required|string|max:50',
            'department_id'  => 'required|exists:departments,id',
        ]);

        $user = Auth::user();

        // Update user profile fields
        $user->designation   = $request->designation;
        $user->employee_code = $request->employee_code;
        $user->status        = 'pending'; // Remain pending until approved by Admin
        $user->save();

        // Create appointment record linking faculty to department
        Appointment::create([
            'user_id'     => $user->id,
            'role'        => Appointment::ROLE_FACULTY,   // constant in Appointment model
            'scope_type'  => Appointment::SCOPE_FACULTY,  // constant in Appointment model
            'scope_id'    => $request->department_id,
            'status'      => 'active',
            'assigned_by' => null, // Will be set when formally assigned by admin
        ]);

        Auth::logout(); // Force re-login to trigger middleware again
        return redirect()->route('faculty.login.form')
            ->with('success', 'Profile completed and linked to your department. Please wait for your Program Chair to approve your account.');
    }
}
