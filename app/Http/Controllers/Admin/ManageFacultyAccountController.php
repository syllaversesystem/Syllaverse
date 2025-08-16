<?php
// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/ManageFacultyAccountController.php
// * Description: Handles viewing, approving, and rejecting faculty accounts (Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-16] Updated approve() to auto-create faculty appointment so login
//              does not redirect to Complete Profile after approval.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class ManageFacultyAccountController extends Controller
{
    /**
     * Show all faculty accounts (pending, approved, rejected).
     */
    public function index()
    {
        $pendingFaculty  = User::where('role', 'faculty')->where('status', 'pending')->orderBy('name')->get();
        $approvedFaculty = User::where('role', 'faculty')->where('status', 'active')->orderBy('name')->get();
        $rejectedFaculty = User::where('role', 'faculty')->where('status', 'rejected')->orderBy('name')->get();

        return view('admin.manage-accounts.index', compact('pendingFaculty', 'approvedFaculty', 'rejectedFaculty'));
    }

    /**
     * Approve a faculty account and create an appointment if missing.
     */
    public function approve($id)
    {
        $admin = Auth::user();

        if ($admin->role !== 'admin') {
            abort(403, 'Only Program Chairs (Admin) can approve accounts.');
        }

        $user = User::findOrFail($id);

        if ($user->role !== 'faculty') {
            return back()->with('error', 'Only faculty accounts can be approved.');
        }

        // ✅ Set status to active
        $user->status = 'active';
        $user->save();

        // ✅ Create faculty appointment if none exists
        if (!$user->appointments()->exists()) {
            Appointment::create([
                'user_id'     => $user->id,
                'role'        => Appointment::ROLE_FACULTY,   // Define in Appointment model
                'scope_type'  => Appointment::SCOPE_FACULTY,  // Define in Appointment model
                'scope_id'    => 0,                           // Or link to department/program
                'status'      => 'active',
                'assigned_by' => $admin->id,
            ]);
        }

        return back()->with('success', 'Faculty approved successfully.');
    }

    /**
     * Reject a faculty account.
     */
    public function reject($id)
    {
        $admin = Auth::user();

        if ($admin->role !== 'admin') {
            abort(403, 'Only Program Chairs (Admin) can reject accounts.');
        }

        $user = User::findOrFail($id);

        if ($user->role !== 'faculty') {
            return back()->with('error', 'Only faculty accounts can be rejected.');
        }

        $user->status = 'rejected';
        $user->save();

        return back()->with('error', 'Faculty account rejected.');
    }
}
