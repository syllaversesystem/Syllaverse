<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/AppointmentController.php
// * Description: Superadmin tools to grant/change/end chair appointments (Dept/Program) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – create/update/end appointments with conflict replacement and audit-friendly flow.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    // ░░░ START: Create Appointment ░░░
    /**
     * Create a new chair appointment for an Admin.
     * Plain-English: This grants Dept/Program Chair power to the chosen admin, ending any conflicting active chair.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'role'          => ['required', 'in:' . Appointment::ROLE_DEPT . ',' . Appointment::ROLE_PROG],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'program_id'    => ['nullable', 'integer', 'exists:programs,id'],
            'start_at'      => ['nullable', 'date'],
        ]);

        // Ensure user is an admin
        $user = User::findOrFail($data['user_id']);
        if ($user->role !== 'admin') {
            return back()->with('error', 'Only Admin accounts can receive chair appointments.');
        }

        // If role is Program Chair, program is required and must belong to department
        if ($data['role'] === Appointment::ROLE_PROG) {
            if (empty($data['program_id'])) {
                return back()->withErrors(['program_id' => 'Program is required for a Program Chair appointment.']);
            }
            $program = Program::findOrFail((int) $data['program_id']);
            if ((int) $program->department_id !== (int) $data['department_id']) {
                return back()->withErrors(['program_id' => 'Selected program does not belong to the selected department.']);
            }
        } else {
            // Department Chair must not specify a program
            $data['program_id'] = null;
            Department::findOrFail((int) $data['department_id']); // ensure exists
        }

        $startAt   = $data['start_at'] ? Carbon::parse($data['start_at']) : now();
        $deciderId = $this->resolveDeciderId($request); // nullable is OK

        DB::transaction(function () use ($data, $user, $startAt, $deciderId) {
            [$scopeType, $scopeId] = $this->mapRoleToScope($data['role'], (int) $data['department_id'], $data['program_id']);

            // End any conflicting active appointment on this scope before granting
            $this->endConflictingActive($data['role'], $scopeType, $scopeId);

            Appointment::create([
                'user_id'     => $user->id,
                'role'        => $data['role'],
                'scope_type'  => $scopeType,
                'scope_id'    => $scopeId,
                'status'      => 'active',
                'start_at'    => $startAt,
                'end_at'      => null,
                'assigned_by' => $deciderId,
            ]);
        });

        return back()->with('success', 'Appointment created successfully.');
    }
    // ░░░ END: Create Appointment ░░░


    // ░░░ START: Update Appointment (End + Recreate) ░░░
    /**
     * Change an existing appointment (role and/or scope).
     * Plain-English: We end the current appointment now and create a new one with the new role/scope.
     * This preserves history instead of silently mutating past records.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $data = $request->validate([
            'role'          => ['required', 'in:' . Appointment::ROLE_DEPT . ',' . Appointment::ROLE_PROG],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'program_id'    => ['nullable', 'integer', 'exists:programs,id'],
            'start_at'      => ['nullable', 'date'], // start of the NEW appointment
        ]);

        if ($data['role'] === Appointment::ROLE_PROG) {
            if (empty($data['program_id'])) {
                return back()->withErrors(['program_id' => 'Program is required for a Program Chair appointment.']);
            }
            $program = Program::findOrFail((int) $data['program_id']);
            if ((int) $program->department_id !== (int) $data['department_id']) {
                return back()->withErrors(['program_id' => 'Selected program does not belong to the selected department.']);
            }
        } else {
            $data['program_id'] = null;
            Department::findOrFail((int) $data['department_id']);
        }

        $startAt   = $data['start_at'] ? Carbon::parse($data['start_at']) : now();
        $deciderId = $this->resolveDeciderId($request); // nullable is OK

        DB::transaction(function () use ($appointment, $data, $startAt, $deciderId) {
            // End the current appointment (if not already)
            if ($appointment->status === 'active') {
                $appointment->endNow();
            }

            // Prepare target scope
            [$scopeType, $scopeId] = $this->mapRoleToScope($data['role'], (int) $data['department_id'], $data['program_id']);

            // End conflicting active appointment at the NEW target
            $this->endConflictingActive($data['role'], $scopeType, $scopeId);

            // Create the new appointment for the same user
            Appointment::create([
                'user_id'     => $appointment->user_id,
                'role'        => $data['role'],
                'scope_type'  => $scopeType,
                'scope_id'    => $scopeId,
                'status'      => 'active',
                'start_at'    => $startAt,
                'end_at'      => null,
                'assigned_by' => $deciderId,
            ]);
        });

        return back()->with('success', 'Appointment updated: previous ended and new appointment created.');
    }
    // ░░░ END: Update Appointment (End + Recreate) ░░░


    // ░░░ START: End Appointment Now ░░░
    /**
     * End an appointment immediately (e.g., turnover or removal).
     * Plain-English: This removes the admin’s chair power for that scope from now onward.
     */
    public function end(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->status !== 'active') {
            return back()->with('info', 'This appointment is already ended.');
        }

        $appointment->endNow();

        return back()->with('success', 'Appointment ended successfully.');
    }
    // ░░░ END: End Appointment Now ░░░


    // ░░░ START: Helpers ░░░
    /** Map logical role to polymorphic scope pair. */
    protected function mapRoleToScope(string $role, int $departmentId, ?int $programId): array
    {
        if ($role === Appointment::ROLE_DEPT) {
            return [Appointment::SCOPE_DEPT, $departmentId];
        }
        // ROLE_PROG
        return [Appointment::SCOPE_PROG, (int) $programId];
    }

    /** Ensure uniqueness: only ONE active chair per (role, scope_type, scope_id). */
    protected function endConflictingActive(string $role, string $scopeType, int $scopeId): void
    {
        $conflicts = Appointment::query()
            ->active()
            ->where('role', $role)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->get();

        foreach ($conflicts as $conflict) {
            $conflict->endNow();
        }
    }

    /** Resolve the acting superadmin’s user id if available; may return null (columns are nullable). */
    protected function resolveDeciderId(Request $request): ?int
    {
        return $request->user()?->id ?? Auth::id() ?? null;
    }
    // ░░░ END: Helpers ░░░
}
