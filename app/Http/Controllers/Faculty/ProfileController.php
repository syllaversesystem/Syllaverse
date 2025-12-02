<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/ProfileController.php
// * Description: Handles Faculty profile completion (pending Superadmin approval) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-10-24] Updated to require superadmin approval for faculty users, similar to admin users
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\ChairRequest;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    // This shows the "Complete Profile" screen and loads Departments for role requests.
    // Faculty can submit designation/employee_code and optionally request leadership roles.
    public function showCompleteProfile(Request $request)
    {
        // ░░░ START: Load Data for Form ░░░
        $user = Auth::user();

        // If somehow unauthenticated, stop here.
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Preload options for simple cascading selects (no extra API needed).
        $departments = Department::orderBy('name')->get(['id', 'name']);
        // ░░░ END: Load Data for Form ░░░

        return view('faculty.complete-profile', compact('user', 'departments'));
    }

    // This saves designation/employee_code and creates ChairRequest rows for requested roles.
    // Faculty is logged out and must wait for superadmin approval before accessing the system.
    public function submitProfile(Request $request)
    {
        // ░░░ START: Identify User ░░░
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        // ░░░ END: Identify User ░░░

        // ░░░ START: Validate Core Fields (HR + Profile) ░░░
        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'designation'     => ['required', 'string', 'max:255'],
            'employee_code'   => ['required', 'string', 'max:255'],

            // Role-request inputs (optional)
            'request_dept_chair' => ['nullable', 'boolean'], // legacy (still accepted silently)
            'request_dept_head'  => ['nullable', 'boolean'],
            'request_vcaa'       => ['nullable', 'boolean'],
            'request_assoc_vcaa' => ['nullable', 'boolean'],
            'request_dean'       => ['nullable', 'boolean'],
            'request_assoc_dean' => ['nullable', 'boolean'],
            'request_faculty'    => ['nullable', 'boolean'],

            // Scope inputs (conditionally required in logic below)
            'department_id'        => ['nullable', 'integer', 'exists:departments,id'],
            'faculty_department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);
        // ░░░ END: Validate Core Fields (HR) ░░░

        // ░░░ START: Interpret Role Requests ░░░
        // Accept either legacy dept chair or new dept head flag
        $wantsDept = (bool) ($request->boolean('request_dept_chair') || $request->boolean('request_dept_head'));
        $wantsVcaa = (bool) $request->boolean('request_vcaa');
        $wantsAssocVcaa = (bool) $request->boolean('request_assoc_vcaa');
        $wantsDean = (bool) $request->boolean('request_dean');
        $wantsAssocDean = (bool) $request->boolean('request_assoc_dean');
        // Enforce mutual exclusion on server side: cannot request both Dean and Associate Dean
        if ($wantsDean && $wantsAssocDean) {
            return back()
                ->withErrors(['request_dean' => 'Select either Dean OR Associate Dean, not both.'])
                ->withInput();
        }
        $wantsFaculty = (bool) $request->boolean('request_faculty');
        // Leadership roles (department-scoped)
        $hasLeadershipRole = $wantsDept || $wantsDean || $wantsAssocDean;
        // ░░░ END: Interpret Role Requests ░░░

        // ░░░ START: Conditional Validation for Scope ░░░
        if ($wantsDept && !$request->filled('department_id')) {
            return back()
                ->withErrors(['department_id' => 'Please select a department for the Department Chair request.'])
                ->withInput();
        }

        // Require department when requesting Dean or Associate Dean (department-scoped roles)
        if (($wantsDean || $wantsAssocDean) && !$request->filled('department_id')) {
            return back()
                ->withErrors(['department_id' => 'Please select your department for the Dean/Associate Dean role request.'])
                ->withInput();
        }

        // Require department when requesting Faculty role
        // If leadership role is requested, we'll mirror that department to the Faculty role automatically.
        if ($wantsFaculty && !$hasLeadershipRole && !$request->filled('faculty_department_id')) {
            return back()
                ->withErrors(['faculty_department_id' => 'Please select your department for the Faculty role.'])
                ->withInput();
        }
        // ░░░ END: Conditional Validation for Scope ░░░

        // ░░░ START: Save & Create Requests (Transaction) ░░░
        DB::transaction(function () use ($user, $request, $wantsDept, $wantsVcaa, $wantsAssocVcaa, $wantsDean, $wantsAssocDean, $wantsFaculty, $hasLeadershipRole) {
            // -- Save profile and HR fields on the user
            $userPayload = [
                'name'          => $request->input('name'),
                'email'         => $request->input('email'),
                'designation'   => $request->input('designation'),
                'employee_code' => $request->input('employee_code'),
                'status'        => 'pending', // Set status to pending for superadmin approval
            ];

            // If the user requested Dean or Associate Dean and selected a department, persist that department on the user record.
            if (($wantsDean || $wantsAssocDean) && $request->filled('department_id')) {
                $userPayload['department_id'] = (int) $request->input('department_id');
            }

            $user->update($userPayload);

            // -- Create Department Head request (dedupe pending)
            if ($wantsDept) {
                $deptId = (int) $request->input('department_id');
                ChairRequest::firstOrCreate(
                    [
                        'user_id'        => $user->id,
                        'requested_role' => ChairRequest::ROLE_DEPT_HEAD,
                        'department_id'  => $deptId,
                        'program_id'     => null,
                        'status'         => ChairRequest::STATUS_PENDING,
                    ],
                    []
                );
            }

            // -- Create institution-level requests.
            // Dean and Associate Dean are department-scoped; VCAA and Associate VCAA are institution-wide.
            $deptId = $request->input('department_id');

            // VCAA and Associate VCAA have institution-wide authority (department_id = null)
            if ($wantsVcaa) {
                ChairRequest::firstOrCreate(
                    [
                        'user_id'        => $user->id,
                        'requested_role' => ChairRequest::ROLE_VCAA,
                        'department_id'  => null, // Institution-wide authority
                        'program_id'     => null,
                        'status'         => ChairRequest::STATUS_PENDING,
                    ],
                    []
                );
            }

            if ($wantsAssocVcaa) {
                ChairRequest::firstOrCreate(
                    [
                        'user_id'        => $user->id,
                        'requested_role' => ChairRequest::ROLE_ASSOC_VCAA,
                        'department_id'  => null, // Institution-wide authority
                        'program_id'     => null,
                        'status'         => ChairRequest::STATUS_PENDING,
                    ],
                    []
                );
            }

            // Dean requires a specific department
            if ($wantsDean) {
                ChairRequest::firstOrCreate(
                    [
                        'user_id'        => $user->id,
                        'requested_role' => ChairRequest::ROLE_DEAN,
                        'department_id'  => $deptId, // Department-specific authority
                        'program_id'     => null,
                        'status'         => ChairRequest::STATUS_PENDING,
                    ],
                    []
                );
            }

            // Associate Dean requires a specific department
            if ($wantsAssocDean) {
                ChairRequest::firstOrCreate(
                    [
                        'user_id'        => $user->id,
                        'requested_role' => ChairRequest::ROLE_ASSOC_DEAN,
                        'department_id'  => $deptId, // Department-specific authority
                        'program_id'     => null,
                        'status'         => ChairRequest::STATUS_PENDING,
                    ],
                    []
                );
            }

            // Faculty Member role (standard faculty without administrative responsibilities)
            // Only create when explicitly requested, do NOT auto-include with leadership.
            if ($wantsFaculty) {
                $facultyDeptId = $request->input('faculty_department_id');
                ChairRequest::firstOrCreate(
                    [
                        'user_id'        => $user->id,
                        'requested_role' => ChairRequest::ROLE_FACULTY,
                        'department_id'  => $facultyDeptId,
                        'program_id'     => null,
                        'status'         => ChairRequest::STATUS_PENDING,
                    ],
                    []
                );
            }
        });
        // ░░░ END: Save & Create Requests (Transaction) ░░░

        // ░░░ START: Response ░░░
        Auth::logout(); // Logout user until superadmin approval

        return redirect()
            ->route('faculty.login.form')
            ->withErrors([
                'approval' => 'Your account is pending approval. You will be notified once approved.',
            ]);
        // ░░░ END: Response ░░░
    }
}
