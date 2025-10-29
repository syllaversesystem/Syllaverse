<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/ManageAdminController.php
// * Description: Handles admin account approval and data loading for Manage Accounts (Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Loaded ChairRequest datasets (pending/approved/rejected) + Programs for Superadmin review UI.
// [2025-08-11] Update – approve/reject now support AJAX JSON responses to avoid tab reset;
//              reject returns { removed_admin_id } so the row can be removed in-place.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;       // ✅ Added
use App\Models\ChairRequest;  // ✅ Added
use App\Models\Appointment;   // ✅ Added for appointment deletion
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ManageAdminController extends Controller
{
    // ░░░ START: Index – load datasets for Manage Accounts page ░░░
    /** Show Manage Accounts datasets (admins, faculty/students, chairs, taxonomies). */
    public function index()
    {
        // Only show pending admins who have completed their profiles (designation and employee_code filled)
        $pendingAdmins  = User::where('role','admin')
                             ->where('status','pending')
                             ->whereNotNull('designation')
                             ->whereNotNull('employee_code')
                             ->get();
        $approvedAdmins = User::where('role','admin')->where('status','active')->get();
        $rejectedAdmins = User::where('role','admin')->where('status','rejected')->get();

        // Faculty users for centralized management
        $pendingFaculty = User::where('role','faculty')
                             ->where('status','pending')
                             ->whereNotNull('designation')
                             ->whereNotNull('employee_code')
                             ->get();
        $approvedFaculty = User::where('role','faculty')->where('status','active')->get();
        $rejectedFaculty = User::where('role','faculty')->where('status','rejected')->get();

        $students = User::where('role','student')->get();

        $departments = Department::orderBy('name')->get();
        $programs    = Program::orderBy('name')->get(); // ✅ add this

        // Include all role requests (including faculty) for superadmin management
        $pendingChairRequests = ChairRequest::with(['user','department','program'])
            ->where('status','pending')
            ->get();

        return view('superadmin.manage-accounts.index', compact(
            'pendingAdmins',
            'approvedAdmins',
            'rejectedAdmins',
            'pendingFaculty',
            'approvedFaculty',
            'rejectedFaculty',
            'students',
            'departments',
            'programs',              // ✅ pass it
            'pendingChairRequests'
        ));
    }
    // ░░░ END: Index ░░░


    // ░░░ START: Approve – supports AJAX JSON to keep current tab ░░░
    /**
     * Approve an admin (status → active).
     * Plain-English: This marks the admin as active. If called via AJAX, return JSON so the UI can update without reloading.
     */
    public function approve(Request $request, $id): JsonResponse|RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            $user->status = 'active';
            $user->save();
        }

        // AJAX path: keep current tab; caller decides what to refresh.
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'       => true,
                'message'  => 'Admin approved successfully.',
                'admin_id' => (int) $user->id,
                'status'   => 'active',
            ]);
        }

        // Non-AJAX fallback: redirect with flash (legacy path).
        return redirect()->back()->with('success', 'Admin approved successfully.');
    }
    // ░░░ END: Approve ░░░


    // ░░░ START: Reject/Revoke – AJAX JSON returns removed row id ░░░
    /**
     * Reject/revoke an admin (status → rejected).
     * Plain-English: This demotes the admin’s status so they disappear from the Approved table.
     * If called via AJAX, we return JSON with the removed row id so the UI can update in-place.
     */
public function reject($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'admin') {
        // Delete all active appointments for this admin before revoking
        $deletedCount = $user->appointments()->where('status', 'active')->delete();
        
        // Set admin status to rejected
        $user->status = 'rejected';
        $user->save();
        
        // Log the deletion for audit purposes
        \Log::info("Admin revoked: User {$user->id} ({$user->name}) - {$deletedCount} appointments deleted");
    }

    // Return JSON if AJAX
    if (request()->ajax()) {
        return response()->json([
            'status'  => 'success',
            'message' => 'Admin revoked successfully. All appointments removed.'
        ]);
    }

    // Fallback for non-AJAX
    return redirect()->back()->with('success', 'Admin revoked successfully. All appointments removed.');
}

/**
 * Suspend an admin account
 */
public function suspend($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'admin') {
        $user->status = 'suspended';
        $user->save();
        
        // Optionally end all appointments
        $user->appointments()->where('status', 'active')->update([
            'status' => 'ended',
            'end_at' => now()
        ]);
    }

    if (request()->ajax()) {
        return response()->json([
            'status' => 'success',
            'message' => 'Admin suspended successfully.'
        ]);
    }

    return redirect()->back()->with('success', 'Admin suspended successfully.');
}

    // ░░░ END: Admin Management ░░░

    // ░░░ START: Faculty Management ░░░

/**
 * Approve a faculty account
 */
public function approveFaculty($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'faculty') {
        $user->status = 'active';
        $user->save();
    }

    if (request()->ajax()) {
        return response()->json([
            'status' => 'success',
            'message' => 'Faculty approved successfully.'
        ]);
    }

    return redirect()->back()->with('success', 'Faculty approved successfully.');
}

/**
 * Reject a faculty account
 */
public function rejectFaculty($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'faculty') {
        $user->status = 'rejected';
        $user->save();
        
        // Remove any active appointments
        $user->appointments()->where('status', 'active')->delete();
    }

    if (request()->ajax()) {
        return response()->json([
            'status' => 'success',
            'message' => 'Faculty rejected successfully.'
        ]);
    }

    return redirect()->back()->with('success', 'Faculty rejected successfully.');
}

/**
 * Suspend a faculty account
 */
public function suspendFaculty($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'faculty') {
        $user->status = 'suspended';
        $user->save();
        
        // End all active appointments
        $user->appointments()->where('status', 'active')->update([
            'status' => 'ended',
            'end_at' => now()
        ]);
    }

    if (request()->ajax()) {
        return response()->json([
            'status' => 'success',
            'message' => 'Faculty suspended successfully.'
        ]);
    }

    return redirect()->back()->with('success', 'Faculty suspended successfully.');
}

/**
 * Reactivate a faculty account
 */
public function reactivateFaculty($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'faculty') {
        $user->status = 'active';
        $user->save();
    }

    if (request()->ajax()) {
        return response()->json([
            'status' => 'success',
            'message' => 'Faculty reactivated successfully.'
        ]);
    }

    return redirect()->back()->with('success', 'Faculty reactivated successfully.');
}

/**
 * Revoke access for a user (admin or faculty) - removes all appointments and sets status to rejected
 */
public function revoke($id)
{
    $user = User::findOrFail($id);

    // Remove all active appointments
    $user->appointments()->where('status', 'active')->delete();

    // Set user status to rejected
    $user->status = 'rejected';
    $user->save();

    if (request()->ajax()) {
        return response()->json([
            'status' => 'success',
            'message' => "Access revoked for {$user->name}. All appointments have been removed and account status set to rejected.",
            'removed_user_id' => $user->id
        ]);
    }

    return redirect()->back()->with('success', "Access revoked for {$user->name}. All appointments have been removed.");
}

    // ░░░ END: Faculty Management ░░░
}
