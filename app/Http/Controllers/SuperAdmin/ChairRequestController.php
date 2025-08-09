<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/ChairRequestController.php
// * Description: Review & decision workflow for Admin chair-role requests (approve → Appointment; reject with notes) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – approval with override support, conflict replacement, and audited decisions.
// [2025-08-08] Removed undefined 'superadmin' guard; resolve decider from request/default guard; allow null decider.
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
        $deptId = (int) ($request->input('department_id') ?: $chairRequest->department_id);
        $progId = $chairRequest->program_id;

        if ($role === ChairRequest::ROLE_PROG) {
            $progId = (int) ($request->input('program_id') ?: $chairRequest->program_id);
            if (!$progId) {
                return back()->withErrors(['program_id' => 'Program is required when approving a Program Chair request.']);
            }
        }

        if ($role === ChairRequest::ROLE_PROG) {
            $program = Program::findOrFail($progId);
            if ((int) $program->department_id !== $deptId) {
                return back()->withErrors(['program_id' => 'Selected program does not belong to the selected department.']);
            }
        } else {
            Department::findOrFail($deptId);
        }

        $startAt   = $request->filled('start_at') ? Carbon::parse($request->input('start_at')) : now();
        $notes     = $request->input('notes');
        $deciderId = $this->resolveDeciderId($request); // may be null

        DB::transaction(function () use ($chairRequest, $role, $deptId, $progId, $startAt, $notes, $deciderId) {
            $scopeType = $role === ChairRequest::ROLE_DEPT ? Appointment::SCOPE_DEPT : Appointment::SCOPE_PROG;
            $scopeId   = $role === ChairRequest::ROLE_DEPT ? $deptId : $progId;

            $this->endConflictingActive($role, $scopeType, $scopeId);

            Appointment::create([
                'user_id'     => $chairRequest->user_id,
                'role'        => $role === ChairRequest::ROLE_DEPT ? Appointment::ROLE_DEPT : Appointment::ROLE_PROG,
                'scope_type'  => $scopeType,
                'scope_id'    => $scopeId,
                'status'      => 'active',
                'start_at'    => $startAt,
                'end_at'      => null,
                'assigned_by' => $deciderId, // nullable
            ]);

            $chairRequest->markApproved($deciderId, $notes);

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

        return back()->with('success', 'Chair request rejected.');
    }

    /** Only ONE active chair per (role, scope_type, scope_id). End conflicts before creating a new one. */
    protected function endConflictingActive(string $role, string $scopeType, int $scopeId): void
    {
        $conflicts = Appointment::query()
            ->active()
            ->where('role', $role === ChairRequest::ROLE_DEPT ? Appointment::ROLE_DEPT : Appointment::ROLE_PROG)
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
