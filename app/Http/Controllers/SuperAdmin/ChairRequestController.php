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
            'start_at'      => ['nullable', 'date'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ]);

        $chairRequest = ChairRequest::with(['user', 'department', 'program'])->findOrFail($id);

        if ($chairRequest->status !== ChairRequest::STATUS_PENDING) {
            return back()->with('error', 'This request is not pending anymore.');
        }

        $role   = $chairRequest->requested_role;
        $deptId = $request->filled('department_id') ? (int) $request->input('department_id') : ($chairRequest->department_id ? (int)$chairRequest->department_id : null);
        $progId = $chairRequest->program_id; // kept for historical data but not required

        // Department-scoped roles require a department. Dean is department-scoped; institution-level VCAA/ASSOC_VCAA are not.
        if ($role === ChairRequest::ROLE_DEPT || $role === ChairRequest::ROLE_DEAN) {
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
            if ($role === ChairRequest::ROLE_DEPT) {
                $apptRole  = Appointment::ROLE_DEPT;
                $scopeType = Appointment::SCOPE_DEPT;
                $scopeId   = $deptId;
            } elseif ($role === ChairRequest::ROLE_DEAN) {
                // Dean is department-scoped (one dean per department)
                $apptRole  = Appointment::ROLE_DEAN;
                $scopeType = Appointment::SCOPE_DEPT;
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

        // 🔁 NEW: If this user is an admin whose signup is still pending, and there are no more pending chair-requests,
        // auto-reject the account (per product rule).
        $user = $chairRequest->user;
        if ($user && $user->role === 'admin' && $user->status === 'pending') {
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
