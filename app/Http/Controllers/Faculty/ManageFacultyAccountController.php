<?php
// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/ManageFacultyAccountController.php
// * Description: Handles viewing, approving, and rejecting faculty accounts for Faculty users (Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-10-18] Copied from admin controller and adapted for faculty users
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\ChairRequest;
use Illuminate\Support\Facades\Auth;

class ManageFacultyAccountController extends Controller
{
    /**
     * Show faculty role requests that need approval from the current user.
     * Only shows requests for departments where the current user has Dean or Department Chair role.
     */
    public function index()
    {
        $currentUser = Auth::user();
        
        // Get departments where the current user has administrative authority
        $authorizedDepartmentIds = $currentUser->appointments()
            ->active()
            ->where('scope_type', Appointment::SCOPE_DEPT)
            ->whereIn('role', [Appointment::ROLE_DEAN, Appointment::ROLE_DEPT])
            ->pluck('scope_id')
            ->toArray();

        // Get faculty requests for those departments
        $pendingRequests = ChairRequest::pending()
            ->where('requested_role', ChairRequest::ROLE_FACULTY)
            ->whereIn('department_id', $authorizedDepartmentIds)
            ->with(['user', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedRequests = ChairRequest::approved()
            ->where('requested_role', ChairRequest::ROLE_FACULTY)
            ->whereIn('department_id', $authorizedDepartmentIds)
            ->with(['user', 'department', 'decidedBy'])
            ->orderBy('decided_at', 'desc')
            ->get();

        $rejectedRequests = ChairRequest::rejected()
            ->where('requested_role', ChairRequest::ROLE_FACULTY)
            ->whereIn('department_id', $authorizedDepartmentIds)
            ->with(['user', 'department', 'decidedBy'])
            ->orderBy('decided_at', 'desc')
            ->get();

        return view('faculty.manage-accounts.index', compact('pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Approve a faculty role request and create the appointment.
     */
    public function approve($id)
    {
        $currentUser = Auth::user();
        $chairRequest = ChairRequest::findOrFail($id);

        // Verify the current user has authority to approve this request
        $authorizedDepartmentIds = $currentUser->appointments()
            ->active()
            ->where('scope_type', Appointment::SCOPE_DEPT)
            ->whereIn('role', [Appointment::ROLE_DEAN, Appointment::ROLE_DEPT])
            ->pluck('scope_id')
            ->toArray();

        if (!in_array($chairRequest->department_id, $authorizedDepartmentIds)) {
            abort(403, 'You do not have authority to approve requests for this department.');
        }

        if ($chairRequest->status !== ChairRequest::STATUS_PENDING) {
            return back()->with('error', 'This request has already been processed.');
        }

        // Approve the request
        $chairRequest->markApproved($currentUser->id, 'Approved by department administrator');

        // Create the faculty appointment
        $appointmentPayload = $chairRequest->toAppointmentPayload($currentUser->id);
        Appointment::create($appointmentPayload);

        // Activate the user account
        $user = $chairRequest->user;
        $user->status = 'active';
        $user->save();

        return back()->with('success', 'Faculty request approved successfully. The user has been granted faculty access.');
    }

    /**
     * Reject a faculty role request.
     */
    public function reject($id)
    {
        $currentUser = Auth::user();
        $chairRequest = ChairRequest::findOrFail($id);

        // Verify the current user has authority to reject this request
        $authorizedDepartmentIds = $currentUser->appointments()
            ->active()
            ->where('scope_type', Appointment::SCOPE_DEPT)
            ->whereIn('role', [Appointment::ROLE_DEAN, Appointment::ROLE_DEPT])
            ->pluck('scope_id')
            ->toArray();

        if (!in_array($chairRequest->department_id, $authorizedDepartmentIds)) {
            abort(403, 'You do not have authority to reject requests for this department.');
        }

        if ($chairRequest->status !== ChairRequest::STATUS_PENDING) {
            return back()->with('error', 'This request has already been processed.');
        }

        // Reject the request
        $chairRequest->markRejected($currentUser->id, 'Rejected by department administrator');

        return back()->with('success', 'Faculty request rejected.');
    }
}