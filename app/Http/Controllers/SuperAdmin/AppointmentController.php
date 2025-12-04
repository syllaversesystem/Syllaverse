<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
	/** Normalize incoming role string to canonical Appointment role constant. */
	protected function normalizeRole(string $role): string
	{
		$r = strtoupper(trim($role));

		// Direct matches
		$direct = [
			Appointment::ROLE_DEPT_HEAD,
			Appointment::ROLE_ASSOC_DEAN,
			Appointment::ROLE_CHAIR,
			Appointment::ROLE_FACULTY,
			Appointment::ROLE_DEAN,
			Appointment::ROLE_VCAA,
			Appointment::ROLE_ASSOC_VCAA,
		];
		if (in_array($r, $direct, true)) {
			return $r;
		}

		// Aliases mapping
		$aliases = [
			'DEPT' => Appointment::ROLE_DEPT_HEAD, // legacy dept head naming
			'DEPARTMENT HEAD' => Appointment::ROLE_DEPT_HEAD,
			'DEPARTMENT HEAD (DEAN/HEAD/PRINCIPAL)' => Appointment::ROLE_DEPT_HEAD,
			'HEAD' => Appointment::ROLE_DEPT_HEAD,
			'DEAN' => Appointment::ROLE_DEPT_HEAD, // UI treats DEAN under Department Head umbrella

			'ASSOC DEAN' => Appointment::ROLE_ASSOC_DEAN,
			'ASSOC_DEAN' => Appointment::ROLE_ASSOC_DEAN,
			'ASSOCIATE DEAN' => Appointment::ROLE_ASSOC_DEAN,

			'CHAIR' => Appointment::ROLE_CHAIR,
			'CHAIRPERSON' => Appointment::ROLE_CHAIR,
			'DEPARTMENT CHAIR' => Appointment::ROLE_CHAIR,
			'DEPARTMENT CHAIRPERSON' => Appointment::ROLE_CHAIR,
			'PROGRAM CHAIR' => Appointment::ROLE_CHAIR, // legacy label maps to chairperson role

			'FACULTY' => Appointment::ROLE_FACULTY,
		];

		return $aliases[$r] ?? $r;
	}

	/** Create a new appointment (AJAX-friendly). */
	public function store(Request $request): JsonResponse
	{
		$data = $request->validate([
			'user_id' => ['required','integer','exists:users,id'],
			'role' => ['required','string'],
			'department_id' => ['nullable','integer','exists:departments,id'],
		]);

		$user = User::findOrFail($data['user_id']);
		$role = $this->normalizeRole($data['role']);

		// Determine scope
		$deptId = $data['department_id'] ?? null;
		$requiresDept = in_array($role, [
			Appointment::ROLE_DEPT_HEAD,
			Appointment::ROLE_ASSOC_DEAN,
			Appointment::ROLE_CHAIR,
			Appointment::ROLE_FACULTY,
		], true);

		if ($requiresDept && empty($deptId)) {
			return response()->json(['ok' => false, 'message' => 'Department is required for this role.'], 422);
		}

		if ($deptId) {
			Department::findOrFail($deptId);
		}

		$appt = new Appointment();
		$appt->user_id = $user->id;
		$appt->setRoleAndScope($role, $deptId, null);
		$appt->assigned_by = auth()->id();
		$appt->save();

		$departmentName = null;
		$programCount = null;
		if ($appt->scope_type === Appointment::SCOPE_DEPT && $appt->scope_id) {
			$dept = Department::find($appt->scope_id);
			$departmentName = $dept?->name;
			if (class_exists(\App\Models\Program::class)) {
				$programCount = \App\Models\Program::where('department_id', $appt->scope_id)->count();
			}
		}

		return response()->json([
			'ok' => true,
			'success' => true,
			'appointment' => $appt,
			'department_name' => $departmentName,
			'program_count' => $programCount,
		]);
	}

	/** Update an appointment role/scope. */
	public function update(Request $request, Appointment $appointment): JsonResponse
	{
		$data = $request->validate([
			'role' => ['required','string'],
			'department_id' => ['nullable','integer','exists:departments,id'],
		]);

		$role = $this->normalizeRole($data['role']);
		$deptId = $data['department_id'] ?? null;

		$requiresDept = in_array($role, [
			Appointment::ROLE_DEPT_HEAD,
			Appointment::ROLE_ASSOC_DEAN,
			Appointment::ROLE_CHAIR,
			Appointment::ROLE_FACULTY,
		], true);
		if ($requiresDept && empty($deptId)) {
			return response()->json(['ok' => false, 'message' => 'Department is required for this role.'], 422);
		}
		if ($deptId) {
			Department::findOrFail($deptId);
		}

		$appointment->setRoleAndScope($role, $deptId, null)->save();

		$departmentName = null;
		$programCount = null;
		if ($appointment->scope_type === Appointment::SCOPE_DEPT && $appointment->scope_id) {
			$dept = Department::find($appointment->scope_id);
			$departmentName = $dept?->name;
			if (class_exists(\App\Models\Program::class)) {
				$programCount = \App\Models\Program::where('department_id', $appointment->scope_id)->count();
			}
		}

		return response()->json([
			'ok' => true,
			'success' => true,
			'appointment' => $appointment,
			'department_name' => $departmentName,
			'program_count' => $programCount,
		]);
	}

	/** End an appointment now. */
	public function end(Appointment $appointment): JsonResponse
	{
		$appointment->endNow();
		return response()->json(['ok' => true, 'success' => true, 'appointment' => $appointment]);
	}

	/** Permanently delete an appointment. */
	public function destroy(Appointment $appointment): JsonResponse
	{
		$userId = $appointment->user_id;
		$deletedDeptId = ($appointment->scope_type === Appointment::SCOPE_DEPT) ? $appointment->scope_id : null;
		$appointment->delete();

		// If user has no active appointments left, auto-create a Faculty role scoped to the last deleted department
		$hasActive = Appointment::where('user_id', $userId)->active()->exists();
		if (!$hasActive && $deletedDeptId) {
			$appt = new Appointment();
			$appt->user_id = $userId;
			$appt->setRoleAndScope(Appointment::ROLE_FACULTY, $deletedDeptId, null);
			$appt->assigned_by = auth()->id();
			$appt->save();

			$departmentName = null;
			$programCount = null;
			$dept = Department::find($deletedDeptId);
			$departmentName = $dept?->name;
			if (class_exists(\App\Models\Program::class)) {
				$programCount = \App\Models\Program::where('department_id', $deletedDeptId)->count();
			}

			return response()->json([
				'ok' => true,
				'success' => true,
				'appointment' => $appt,
				'department_name' => $departmentName,
				'program_count' => $programCount,
			]);
		}

		return response()->json(['ok' => true, 'success' => true]);
	}
}

