<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/ManageAdminController.php
// * Description: Handles admin account approval and data loading for Manage Accounts (Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Loaded ChairRequest datasets (pending/approved/rejected) + Programs for Superadmin review UI.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;       // ✅ Added
use App\Models\ChairRequest;  // ✅ Added

class ManageAdminController extends Controller
{
    public function index()
{
    $pendingAdmins  = User::where('role','admin')->where('status','pending')->get();
    $approvedAdmins = User::where('role','admin')->where('status','active')->get();
    $rejectedAdmins = User::where('role','admin')->where('status','rejected')->get();

    $faculty  = User::where('role','faculty')->get();
    $students = User::where('role','student')->get();

    $departments = Department::orderBy('name')->get();
    $programs    = Program::orderBy('name')->get(); // ✅ add this

    $pendingChairRequests = ChairRequest::with(['user','department','program'])
        ->where('status','pending')
        ->get();

    return view('superadmin.manage-accounts.index', compact(
        'pendingAdmins',
        'approvedAdmins',
        'rejectedAdmins',
        'faculty',
        'students',
        'departments',
        'programs',              // ✅ pass it
        'pendingChairRequests'
    ));
}

    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            $user->status = 'active';
            $user->save();
        }

        return redirect()->back()->with('success', 'Admin approved successfully.');
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            $user->status = 'rejected';
            $user->save();
        }

        return redirect()->back()->with('success', 'Admin rejected/revoked successfully.');
    }
}
