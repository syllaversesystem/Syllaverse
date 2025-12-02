<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/ChairRequestController.php
// * Description: Review & decision workflow for Admin chair-role requests (approve → Appointment; reject with notes) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – approval with override support, conflict replacement, and audited decisions.
// [2025-08-08] Removed undefined 'superadmin' guard; resolve decider from request/default guard; allow null decider.
// [2025-08-11] Auto-reject admin account when all their pending chair-requests are rejected and signup is still pending.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ChairRequest;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChairRequestController extends Controller
{
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'program_id'    => ['nullable', 'integer', 'exists:programs,id'],
            'start_at'      => ['nullable', 'date'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ]);

        $chairRequest = ChairRequest::with(['user', 'department', 'program'])->findOrFail($id);

        if ($chairRequest->status !== ChairRequest::STATUS_PENDING) {
            return back()->with('error', 'This request is not pending anymore.');
        }

        $role   = $chairRequest->requested_role;
        $deptId = $request->filled('department_id') ? (int) $request->input('department_id') : ($chairRequest->department_id ? (int)$chairRequest->department_id : null);
        $progId = $request->filled('program_id') ? (int) $request->input('program_id') : ($chairRequest->program_id ? (int)$chairRequest->program_id : null);

        // If this is a program-scoped request, ensure we have a program id available (try to infer
        // from department when possible). If not, bail with a validation error so the approver can
        // supply a program in the approval form.
        if ($role === ChairRequest::ROLE_PROG) {
            if (empty($progId) && !empty($deptId)) {
                $progId = Program::where('department_id', $deptId)->value('id');
            }
            if (empty($progId)) {
                return back()->withErrors(['program_id' => 'Program is required when approving a Program Chair request.']);
            }
        }

        // Department-scoped roles require a department.
        // Faculty, Chairperson (CHAIR), Dept Head (DEPT_HEAD), Dean, Associate Dean are department-scoped;
        // institution-level VCAA/ASSOC_VCAA are not.
        if (in_array($role, [ChairRequest::ROLE_DEPT, ChairRequest::ROLE_CHAIR, ChairRequest::ROLE_DEPT_HEAD, ChairRequest::ROLE_DEAN, ChairRequest::ROLE_ASSOC_DEAN, ChairRequest::ROLE_FACULTY], true)) {
            if (empty($deptId)) {
                return back()->withErrors(['department_id' => 'Department is required when approving this request.']);
            }
            Department::findOrFail($deptId);
        } else {
            // Institution-level roles (VCAA / ASSOC_VCAA) do not require department/program validation.
        }

        $startAt   = $request->filled('start_at') ? Carbon::parse($request->input('start_at')) : now();
        $notes     = $request->input('notes');
        $deciderId = $this->resolveDeciderId($request); // may be null

        DB::transaction(function () use ($chairRequest, $role, $deptId, $progId, $startAt, $notes, $deciderId) {
            // Determine appointment role and scope based on the chair request type.
            if ($role === ChairRequest::ROLE_DEPT || $role === ChairRequest::ROLE_CHAIR) {
                // Chairperson ⇒ Department Chair appointment
                $apptRole  = Appointment::ROLE_DEPT;
                $scopeType = Appointment::SCOPE_DEPT;
                $scopeId   = $deptId;
            } elseif ($role === ChairRequest::ROLE_DEPT_HEAD || $role === ChairRequest::ROLE_DEAN) {
                // Dean is department-scoped (one dean per department)
                $apptRole  = Appointment::ROLE_DEAN;
                $scopeType = Appointment::SCOPE_DEPT;
                $scopeId   = $deptId;
            } elseif ($role === ChairRequest::ROLE_ASSOC_DEAN) {
                // Associate Dean is department-scoped (assists dean within department)
                $apptRole  = Appointment::ROLE_ASSOC_DEAN;
                $scopeType = Appointment::SCOPE_DEPT;
                $scopeId   = $deptId;
            } elseif ($role === ChairRequest::ROLE_PROG) {
                // Program-scoped appointment
                $apptRole  = Appointment::ROLE_PROG;
                $scopeType = Appointment::SCOPE_PROG;
                $scopeId   = $progId;
            } elseif ($role === ChairRequest::ROLE_FACULTY) {
                // Faculty role uses SCOPE_FACULTY but scope_id references the department
                $apptRole  = Appointment::ROLE_FACULTY;
                $scopeType = Appointment::SCOPE_FACULTY;
                $scopeId   = $deptId;
            } else {
                // Institution-level roles: use the same role string and institution scope.
                // Use a sentinel scope_id (0) to indicate "institution" so DB inserts don't fail
                // when scope_id is non-nullable in some environments.
                $apptRole  = $role; // matches Appointment::ROLE_VCAA, etc.
                $scopeType = Appointment::SCOPE_INSTITUTION;
                $scopeId   = 0;
            }

            $this->endConflictingActive($apptRole, $scopeType, $scopeId);

            Appointment::create([
                'user_id'     => $chairRequest->user_id,
                'role'        => $apptRole,
                'scope_type'  => $scopeType,
                'scope_id'    => $scopeId,
                'status'      => 'active',
                'start_at'    => $startAt,
                'end_at'      => null,
                'assigned_by' => $deciderId, // nullable
            ]);

            $chairRequest->markApproved($deciderId, $notes);

            // If user was still pending, activating any chair request should accept the account
            if ($chairRequest->user && $chairRequest->user->status !== 'active') {
                $chairRequest->user->status = 'active';
                $chairRequest->user->save();
            }
        });

        return back()->with('success', 'Chair request approved and appointment created.');
    }

    public function reject(Request $request, int $id)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $chairRequest = ChairRequest::with('user')->findOrFail($id);

        if ($chairRequest->status !== ChairRequest::STATUS_PENDING) {
            return back()->with('error', 'This request is not pending anymore.');
        }

        $deciderId = $this->resolveDeciderId($request); // may be null
        $chairRequest->markRejected($deciderId, $request->input('notes'));

        // 🔁 NEW: If this user's signup is still pending, and there are no more pending chair-requests,
        // auto-reject the account (per product rule).
        $user = $chairRequest->user;
        if ($user && $user->status === 'pending') {
            $remaining = ChairRequest::where('user_id', $user->id)
                ->where('status', ChairRequest::STATUS_PENDING)
                ->count();

            if ($remaining === 0) {
                $user->status = 'rejected';
                $user->save();
            }
        }

        return back()->with('success', 'Chair request rejected.');
    }

    /**
     * Approve all pending chair requests for a specific user.
     */
    public function approveAll(Request $request, int $userId)
    {
        $pendingRequests = ChairRequest::with(['user', 'department', 'program'])
            ->where('user_id', $userId)
            ->where('status', ChairRequest::STATUS_PENDING)
            ->get();

        if ($pendingRequests->isEmpty()) {
            return back()->with('error', 'No pending requests found for this user.');
        }

        // Validate that department-scoped roles have a department before batch approval
            $deptScoped = [ChairRequest::ROLE_DEPT, ChairRequest::ROLE_DEPT_HEAD, ChairRequest::ROLE_DEAN, ChairRequest::ROLE_ASSOC_DEAN, ChairRequest::ROLE_FACULTY];
        $missingDept = $pendingRequests->firstWhere(function($r) use ($deptScoped) {
            return in_array($r->requested_role, $deptScoped, true) && empty($r->department_id);
        });
        if ($missingDept) {
            return back()->withErrors([
                'department_id' => 'One or more department-scoped requests (e.g., Department Head/Dean/Associate Dean/Faculty) are missing a department. Please set a department for each before approving all.'
            ]);
        }

        $deciderId = $this->resolveDeciderId($request);
        $startAt = now();
        $approvedCount = 0;

        DB::transaction(function () use ($pendingRequests, $deciderId, $startAt, &$approvedCount) {
            foreach ($pendingRequests as $chairRequest) {
                $role = $chairRequest->requested_role;
                $deptId = $chairRequest->department_id;
                $progId = $chairRequest->program_id;

                // Determine appointment details based on role
                    if ($role === ChairRequest::ROLE_DEPT || $role === ChairRequest::ROLE_CHAIR) {
                        // Chairperson ⇒ Department Chair appointment
                        $apptRole = Appointment::ROLE_DEPT;
                        $scopeType = Appointment::SCOPE_DEPT;
                        $scopeId = $deptId; // must be non-null due to pre-validation
                    } elseif ($role === ChairRequest::ROLE_DEPT_HEAD || $role === ChairRequest::ROLE_DEAN) {
                        // Dept Head (Dean/Head/Principal) ⇒ Dean appointment
                        $apptRole = Appointment::ROLE_DEAN;
                        $scopeType = Appointment::SCOPE_DEPT;
                        $scopeId = $deptId; // must be non-null due to pre-validation
                    } elseif ($role === ChairRequest::ROLE_ASSOC_DEAN) {
                    $apptRole = Appointment::ROLE_ASSOC_DEAN;
                    $scopeType = Appointment::SCOPE_DEPT;
                    $scopeId = $deptId; // must be non-null due to pre-validation
                } elseif ($role === ChairRequest::ROLE_PROG) {
                    $apptRole = Appointment::ROLE_PROG;
                    $scopeType = Appointment::SCOPE_PROG;
                    $scopeId = $progId;
                } elseif ($role === ChairRequest::ROLE_FACULTY) {
                    $apptRole = Appointment::ROLE_FACULTY;
                    $scopeType = Appointment::SCOPE_FACULTY;
                    $scopeId = $deptId; // must be non-null due to pre-validation
                } else {
                    // Institution-level roles (VCAA, Associate VCAA)
                    $apptRole = $role;
                    $scopeType = Appointment::SCOPE_INSTITUTION;
                    $scopeId = 0;
                }

                // End conflicting appointments
                $this->endConflictingActive($apptRole, $scopeType, $scopeId);

                // Create new appointment
                Appointment::create([
                    'user_id' => $chairRequest->user_id,
                    'role' => $apptRole,
                    'scope_type' => $scopeType,
                    'scope_id' => $scopeId,
                    'status' => 'active',
                    'start_at' => $startAt,
                    'end_at' => null,
                    'assigned_by' => $deciderId,
                ]);

                // Mark request as approved
                $chairRequest->markApproved($deciderId, 'Approved via batch approval');
                $approvedCount++;
            }

            // Activate user account if still pending
            $user = $pendingRequests->first()->user;
            if ($user && $user->status !== 'active') {
                $user->status = 'active';
                $user->save();
            }
        });

        return back()->with('success', "Successfully approved all {$approvedCount} chair requests for {$pendingRequests->first()->user->name}.");
    }

    /**
     * Reject all pending chair requests for a specific user.
     */
    public function rejectAll(Request $request, int $userId)
    {
        $pendingRequests = ChairRequest::with(['user', 'department', 'program'])
            ->where('user_id', $userId)
            ->where('status', ChairRequest::STATUS_PENDING)
            ->get();

        if ($pendingRequests->isEmpty()) {
            return back()->with('error', 'No pending requests found for this user.');
        }

        $deciderId = $this->resolveDeciderId($request);
        $rejectedCount = 0;

        DB::transaction(function () use ($pendingRequests, $deciderId, $userId, &$rejectedCount) {
            foreach ($pendingRequests as $chairRequest) {
                // Mark request as rejected
                $chairRequest->markRejected($deciderId, 'Rejected via batch rejection');
                $rejectedCount++;
            }

            // Check if user should be auto-rejected (if signup is still pending and all requests are rejected)
            $user = $pendingRequests->first()->user;
            if ($user && $user->status === 'pending') {
                // Check if user has any other pending requests
                $remainingPendingRequests = ChairRequest::where('user_id', $userId)
                    ->where('status', ChairRequest::STATUS_PENDING)
                    ->count();

                if ($remainingPendingRequests === 0) {
                    // No pending requests left, reject the user account
                    $user->status = 'rejected';
                    $user->save();
                }
            }
        });

        return back()->with('success', "Successfully rejected all {$rejectedCount} chair requests for {$pendingRequests->first()->user->name}.");
    }

    /** Only ONE active chair per (role, scope_type, scope_id). End conflicts before creating a new one.
     *
     * Note: institution-level appointments pass null for scope_id, so this accepts a nullable int.
     */
    protected function endConflictingActive(string $role, string $scopeType, ?int $scopeId): void
    {
        $conflicts = Appointment::query()
            ->active()
            ->where('role', $role === ChairRequest::ROLE_DEPT ? Appointment::ROLE_DEPT : $role)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->get();

        foreach ($conflicts as $conflict) {
            $conflict->endNow();
        }
    }

    /** Resolve decider ID from the current request/default guard; returns null if not available. */
    protected function resolveDeciderId(Request $request): ?int
    {
        return $request->user()?->id ?? Auth::id() ?? null;
    }
}
