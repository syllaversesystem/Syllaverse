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
    /**
     * Get the user's last known department id based on department-scoped appointments.
     */
    protected function getLastDepartmentId(User $user): ?int
    {
        // Get the most recent appointment and derive department id from its scope
        $last = $user->appointments()
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$last) {
            return null;
        }

        // Map various role/scope combos to a department id
        // FACULTY: scope_type = Faculty, scope_id stores department id
        if ($last->role === Appointment::ROLE_FACULTY) {
            return $last->scope_id ? (int) $last->scope_id : null;
        }

        // Department-scoped leadership roles: scope_type = Department, scope_id is department id
        if (in_array($last->role, [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_ASSOC_DEAN], true)) {
            return $last->scope_id ? (int) $last->scope_id : null;
        }

        // Legacy Program Chair: translate program -> department
        if ($last->role === Appointment::ROLE_PROG && $last->scope_id) {
            $program = Program::find((int) $last->scope_id);
            return $program ? (int) $program->department_id : null;
        }

        // DEAN/VCAA etc. may be institution-scoped; no department to infer
        return null;
    }
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
            // Re-approve as Faculty with last known department
            $lastDeptId = $this->getLastDepartmentId($user);

            // Update user role/status
            $user->role = 'faculty';
            $user->status = 'active';
            $user->save();

            // Create a fresh active Faculty appointment only if we have a department id
            if ($lastDeptId) {
                $appt = new Appointment();
                $appt->user_id = $user->id;
                // Explicitly set role + scope to ensure scope_type and scope_id are valid
                $appt->setRoleAndScope(Appointment::ROLE_FACULTY, $lastDeptId, null);
                $appt->status = 'active';
                $appt->save();
            }
        }

        // AJAX path: keep current tab; caller decides what to refresh.
        if ($request->expectsJson() || $request->ajax()) {
            $deptName = null;
            $deptId = $this->getLastDepartmentId($user);
            if ($deptId) { $deptName = optional(Department::find($deptId))->name; }
            $msg = 'Account re-approved as Faculty.';
            if ($deptName) {
                $msg .= ' Assigned department: '.$deptName.'.';
            } else {
                $msg .= ' No last department found; appointment not created.';
            }
            return response()->json([
                'ok'       => true,
                'message'  => $msg,
                'admin_id' => (int) $user->id,
                'status'   => 'active',
                'needs_department' => $deptName ? false : true,
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
        // End all active appointments for this admin before revoking (preserve history for re-approve)
        $deletedCount = $user->appointments()->where('status', 'active')->update([
            'status' => 'ended',
            'end_at' => now(),
        ]);
        
        // Set admin status to rejected
        $user->status = 'rejected';
        $user->save();
        
        // Log the deletion for audit purposes
        \Log::info("Admin revoked: User {$user->id} ({$user->name}) - {$deletedCount} appointments ended");
    }

    // Return JSON if AJAX
    if (request()->ajax()) {
        return response()->json([
            'status'  => 'success',
            'message' => 'Admin revoked successfully. All active appointments ended.'
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
     * Permanently delete a rejected admin account.
     * Removes related appointments and chair requests, then deletes the user.
     */
    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'admin') {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid role for admin deletion.',
            ], 422);
        }

        if ($user->status !== 'rejected') {
            return response()->json([
                'ok' => false,
                'message' => 'Only rejected accounts can be deleted.',
            ], 422);
        }

        // Cleanup related data
        try {
            $user->appointments()->delete();
            $user->chairRequests()->delete();
        } catch (\Throwable $e) {
            \Log::warning('Cleanup before admin delete failed: '.$e->getMessage());
        }

        $name = $user->name;
        $user->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => "Deleted admin {$name}.",
                'removed_user_id' => (int) $id,
            ]);
        }

        return redirect()->back()->with('success', "Deleted admin {$name}.");
    }

/**
 * Approve a faculty account
 */
public function approveFaculty($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'faculty') {
        $user->status = 'active';
        $user->save();

        // Ensure an active Faculty appointment exists with last known department
        $hasActiveFacultyAppt = $user->appointments()->where('role', Appointment::ROLE_FACULTY)->where('status','active')->exists();
        if (!$hasActiveFacultyAppt) {
                $lastDeptId = $this->getLastDepartmentId($user);
                if ($lastDeptId) {
                    $appt = new Appointment();
                    $appt->user_id = $user->id;
                    // Ensure scope_type + scope_id set coherently for faculty
                    $appt->setRoleAndScope(Appointment::ROLE_FACULTY, $lastDeptId, null);
                    $appt->status = 'active';
                    $appt->save();
                }
        }
    }

    if (request()->ajax()) {
        $deptId = $this->getLastDepartmentId($user);
        $deptName = $deptId ? optional(Department::find($deptId))->name : null;
            $msg = 'Account re-approved as Faculty.';
            if ($deptName) {
                $msg .= ' Assigned department: '.$deptName.'.';
            } else {
                $msg .= ' No last department found; appointment not created.';
            }
        return response()->json([
            'ok' => true,
            'status' => 'success',
                'message' => $msg,
                'needs_department' => $deptName ? false : true,
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
        
        // End any active appointments (preserve history)
        $user->appointments()->where('status', 'active')->update([
            'status' => 'ended',
            'end_at' => now(),
        ]);
    }

    if (request()->ajax()) {
        return response()->json([
            'status' => 'success',
            'message' => 'Faculty rejected successfully. All active appointments ended.'
        ]);
    }

    return redirect()->back()->with('success', 'Faculty rejected successfully.');
}

    /**
     * Permanently delete a rejected faculty account.
     * Removes related appointments and chair requests, then deletes the user.
     */
    public function deleteFaculty(Request $request, $id): JsonResponse|RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'faculty') {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid role for faculty deletion.',
            ], 422);
        }

        if ($user->status !== 'rejected') {
            return response()->json([
                'ok' => false,
                'message' => 'Only rejected accounts can be deleted.',
            ], 422);
        }

        // Cleanup related data
        try {
            $user->appointments()->delete();
            $user->chairRequests()->delete();
        } catch (\Throwable $e) {
            \Log::warning('Cleanup before faculty delete failed: '.$e->getMessage());
        }

        $name = $user->name;
        $user->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => "Deleted faculty {$name}.",
                'removed_user_id' => (int) $id,
            ]);
        }

        return redirect()->back()->with('success', "Deleted faculty {$name}.");
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
    $user->appointments()->where('status', 'active')->update([
        'status' => 'ended',
        'end_at' => now(),
    ]);

    // Set user status to rejected
    $user->status = 'rejected';
    $user->save();

    if (request()->ajax()) {
        return response()->json([
            'status' => 'success',
            'message' => "Access revoked for {$user->name}. All active appointments have been ended and account status set to rejected.",
            'removed_user_id' => $user->id
        ]);
    }

    return redirect()->back()->with('success', "Access revoked for {$user->name}. All active appointments have been ended.");
}

    // ░░░ END: Faculty Management ░░░
}
