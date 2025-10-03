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

    // ░░░ END: Reject/Revoke ░░░
}
