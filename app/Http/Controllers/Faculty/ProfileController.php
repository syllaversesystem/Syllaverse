<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use App\Models\Department;
use App\Models\ChairRequest;

class ProfileController extends Controller
{
	/** Create a pending ChairRequest if an identical pending one doesn't already exist. */
	protected function createPendingRoleRequest(int $userId, string $role, ?int $departmentId = null, ?int $programId = null): void
	{
		$exists = ChairRequest::query()
			->where('user_id', $userId)
			->where('requested_role', $role)
			->when($departmentId !== null, fn($q) => $q->where('department_id', $departmentId))
			->when($programId !== null, fn($q) => $q->where('program_id', $programId))
			->where('status', ChairRequest::STATUS_PENDING)
			->exists();

		if (!$exists) {
			ChairRequest::create([
				'user_id'        => $userId,
				'requested_role' => $role,
				'department_id'  => $departmentId,
				'program_id'     => $programId,
				'status'         => ChairRequest::STATUS_PENDING,
			]);
		}
	}
	/**
	 * Show the 2-step Complete Profile wizard for faculty users.
	 */
	public function showCompleteProfile(Request $request)
	{
		$user = $request->user('faculty');
		if (!$user) {
			return Redirect::route('faculty.login.form');
		}

		$user->loadMissing(['chairRequests.department']);
		$departments = Department::orderBy('name')->get();

		return view('faculty.complete-profile', [
			'user' => $user,
			'departments' => $departments,
		]);
	}

	/**
	 * Handle profile field updates and optional role request creation.
	 */
	public function submitProfile(Request $request)
	{
		$user = $request->user('faculty');
		if (!$user) {
			return Redirect::route('faculty.login.form');
		}

		$hasPending = $user->chairRequests()->where('status', ChairRequest::STATUS_PENDING)->exists();

		$validated = $request->validate([
			'name'           => ['required', 'string', 'max:255'],
			'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
			'designation'    => ['required', 'string', 'max:255'],
			'employee_code'  => ['required', 'string', 'max:255'],

			// Conditional below; validated again when needed
			'department_id'           => ['nullable', 'integer', 'exists:departments,id'],
			'faculty_department_id'   => ['nullable', 'integer', 'exists:departments,id'],
		]);

		// Always allow updating profile fields
		$user->fill([
			'name'          => $validated['name'],
			'email'         => $validated['email'],
			'designation'   => $validated['designation'],
			'employee_code' => $validated['employee_code'],
		])->save();

		// If a pending request exists, do not allow creating another; just return.
		if ($hasPending) {
			return Redirect::route('faculty.login.form')
				->with('status', 'Your profile was updated. You already have a pending role request awaiting decision.')
				->with('pending', 'Your account is pending approval. You will be notified once approved.');
		}

		// UI: request_dean => Department Head; request_dept_head => Chairperson
		$wantsDeptHead   = (bool) $request->boolean('request_dept_head'); // Chairperson
		$wantsDean       = (bool) $request->boolean('request_dean');      // Department Head
		$wantsAssocDean  = (bool) $request->boolean('request_assoc_dean');
		$wantsFaculty    = (bool) $request->boolean('request_faculty');

		$anyLeadership = $wantsDeptHead || $wantsDean || $wantsAssocDean;

		if ($anyLeadership) {
			$request->validate([
				'department_id' => ['required', 'integer', 'exists:departments,id'],
			], ['department_id.required' => 'Please select a department for the selected leadership role.']);

			$deptId = (int) $request->input('department_id');

			if ($wantsDeptHead) {
				// Chairperson => CHAIR
				$this->createPendingRoleRequest($user->id, ChairRequest::ROLE_CHAIR, $deptId);
			}
			if ($wantsDean) {
				// Department Head => DEPT_HEAD
				$this->createPendingRoleRequest($user->id, ChairRequest::ROLE_DEPT_HEAD, $deptId);
			}
			if ($wantsAssocDean) {
				$this->createPendingRoleRequest($user->id, ChairRequest::ROLE_ASSOC_DEAN, $deptId);
			}
		} else if ($wantsFaculty) {
			$request->validate([
				'faculty_department_id' => ['required', 'integer', 'exists:departments,id'],
			], ['faculty_department_id.required' => 'Please select your department for the Faculty request.']);

			$this->createPendingRoleRequest($user->id, ChairRequest::ROLE_FACULTY, (int) $request->input('faculty_department_id'));
		}

		// Log out after submission so superadmin can approve before access.
		Auth::guard('faculty')->logout();

		return Redirect::route('faculty.login.form')
			->with('status', 'Your profile was updated and your role request(s) were submitted for approval. You will be notified once a decision is made.')
			->with('pending', 'Your account is pending approval. You will be notified once approved.');
	}
}

