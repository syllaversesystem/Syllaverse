<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/AppointmentController.php
// * Description: Create/Update/End/Destroy chair appointments – JSON payload for AJAX DOM updates
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-11] AJAX – JSON responses for XHR; redirect+flash for normal posts.
// [2025-08-11] Robust – tolerant role parsing (dept/prog variants) + one-active-per-scope guard.
// [2025-08-11] DOM – returns { admin_id, appointments[] } for client-side rendering (no Blade fragments).
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }

    /** Normalize various role inputs to model constants or null if unknown. */
    protected function normalizeRole(mixed $role): ?string
    {
        $r = strtoupper(trim((string) $role));
        if ($r === Appointment::ROLE_DEPT) return $r;
        if (in_array($r, ['DEPT','DEPARTMENT','DEPARTMENT_CHAIR','0'], true)) return Appointment::ROLE_DEPT;
        // Program Chair removed — do not attempt to map program-like inputs
        if (str_contains($r, 'DEPT')) return Appointment::ROLE_DEPT;
        return null;
    }

    /** Format active appointments for one admin into a lightweight JSON array. */
    protected function buildAppointmentsPayload(User $admin): array
    {
        $admin->loadMissing('appointments');
        $active = $admin->appointments()->active()->get();

        $deptNames = Department::pluck('name', 'id');                                 // id => name

        return $active->map(function (Appointment $a) use ($deptNames) {
            $isDept = $a->role === Appointment::ROLE_DEPT;

            $scopeLabel = $isDept
                ? (string) ($deptNames[$a->scope_id] ?? ('Dept #'.$a->scope_id))
                : ($a->scope_type ? ($a->scope_type . ' #' . $a->scope_id) : 'Institution');

            return [
                'id'          => (int) $a->id,
                'role'        => $a->role,
                'role_label'  => $isDept ? 'Dept Chair' : ($a->role ?? 'Appointment'),
                'is_dept'     => $isDept,
                'scope_id'    => $a->scope_id ? (int) $a->scope_id : null,
                'scope_label' => $scopeLabel,
                'dept_id'     => $isDept ? (int) $a->scope_id : null,
                'program_id'  => null,
            ];
        })->values()->all();
    }

    /** Unified responder. */
    protected function respond(Request $request, bool $ok, string $message, int $status = 200, array $errors = null, array $extra = []): JsonResponse|RedirectResponse
    {
        if ($this->wantsJson($request)) {
            $payload = array_merge(['ok' => $ok, 'message' => $message], $extra);
            if ($errors !== null) $payload['errors'] = $errors;
            return response()->json($payload, $status);
        }

        if ($status === 422 && $errors) {
            return back()->withErrors($errors)->withInput();
        }

        return $ok ? back()->with('success', $message) : back()->with('error', $message);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'role'          => ['required'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);
        if ($v->fails()) {
            return $this->respond($request, false, 'Validation failed.', 422, $v->errors()->toArray());
        }

        $data = $v->validated();
        $role = $this->normalizeRole($data['role']);
        if (!$role) {
            return $this->respond($request, false, 'Invalid role.', 422, ['role' => ['Invalid role value.']]);
        }

        // Only Department Chair requires department; other roles are institution-level.
        $scopeId = $data['department_id'] ?? null;
        if ($role === Appointment::ROLE_DEPT && !$scopeId) {
            return $this->respond($request, false, 'Department is required for Department Chair.', 422, ['department_id' => ['Department is required for Department Chair.']]);
        }

        // One active per role+scope
        $exists = Appointment::active()
            ->where('role', $role)
            ->where('scope_type', $role === Appointment::ROLE_DEPT ? Appointment::SCOPE_DEPT : Appointment::SCOPE_INSTITUTION)
            ->where('scope_id', (int) ($scopeId ?? 0))
            ->exists();

        if ($exists) {
            $field = 'department_id';
            return $this->respond($request, false, 'An active chair already exists for this selection.', 422, [
                $field => ['An active chair already exists for this selection.'],
            ]);
        }

        $appt = new Appointment([
            'user_id' => (int) $data['user_id'],
            'role'    => $role,
            'status'  => 'active',
        ]);
        $appt->setRoleAndScope($role, $data['department_id'] ?? null, null);
        $appt->save();

        $admin = User::findOrFail((int) $data['user_id']);
        $appointments = $this->buildAppointmentsPayload($admin);

        return $this->respond($request, true, 'Appointment created.', 200, null, [
            'admin_id'     => $admin->id,
            'appointments' => $appointments,
        ]);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse|RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'role'          => ['required'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);
        if ($v->fails()) {
            return $this->respond($request, false, 'Validation failed.', 422, $v->errors()->toArray());
        }

        $data = $v->validated();
        $role = $this->normalizeRole($data['role']);
        if (!$role) {
            return $this->respond($request, false, 'Invalid role.', 422, ['role' => ['Invalid role value.']]);
        }

            // Only Department Chair requires department; other roles are institution-level.
            $scopeId = $data['department_id'] ?? null;
            if ($role === Appointment::ROLE_DEPT && !$scopeId) {
                return $this->respond($request, false, 'Department is required for Department Chair.', 422, ['department_id' => ['Department is required for Department Chair.']]);
            }

        $exists = Appointment::active()
            ->where('role', $role)
                ->where('scope_type', $role === Appointment::ROLE_DEPT ? Appointment::SCOPE_DEPT : Appointment::SCOPE_INSTITUTION)
            ->where('scope_id', (int) $scopeId)
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($exists) {
            $field = 'department_id';
            return $this->respond($request, false, 'An active chair already exists for this selection.', 422, [
                $field => ['An active chair already exists for this selection.'],
            ]);
        }

            $appointment->setRoleAndScope($role, $data['department_id'] ?? null, null);
        $appointment->save();

        $admin = $appointment->user()->firstOrFail();
        $appointments = $this->buildAppointmentsPayload($admin);

        return $this->respond($request, true, 'Appointment updated.', 200, null, [
            'admin_id'     => $admin->id,
            'appointments' => $appointments,
        ]);
    }

    public function end(Request $request, Appointment $appointment): JsonResponse|RedirectResponse
    {
        if ($appointment->status !== 'ended') {
            $appointment->endNow();
        }

        $admin = $appointment->user()->firstOrFail();
        $appointments = $this->buildAppointmentsPayload($admin);

        return $this->respond($request, true, 'Appointment ended.', 200, null, [
            'admin_id'     => $admin->id,
            'appointments' => $appointments,
        ]);
    }

    public function destroy(Request $request, Appointment $appointment): JsonResponse|RedirectResponse
    {
        $admin = $appointment->user()->firstOrFail();
        $appointment->delete();

        $appointments = $this->buildAppointmentsPayload($admin);

        return $this->respond($request, true, 'Appointment removed.', 200, null, [
            'admin_id'     => $admin->id,
            'appointments' => $appointments,
        ]);
    }
}
