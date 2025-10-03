<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/ProfileController.php
// * Description: Handles Admin profile completion and chair-role self-requests (pending Superadmin approval) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – show/save profile, validate & store chair requests, guard mismatched dept/program.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChairRequest;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    // This shows the "Complete Profile" screen and loads Departments & Programs for role requests.
    // Admins can submit designation/employee_code and optionally request Dept/Program Chair.
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

    return view('admin.complete-profile', compact('user', 'departments'));
    }

    // This saves designation/employee_code and creates one or two ChairRequest rows (Dept and/or Program Chair).
    // It prevents mismatched dept/program and duplicate pending requests.
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
            'request_dept_chair' => ['nullable', 'boolean'],
            'request_vcaa'       => ['nullable', 'boolean'],
            'request_assoc_vcaa' => ['nullable', 'boolean'],
            'request_dean'       => ['nullable', 'boolean'],

            // Scope inputs (conditionally required in logic below)
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'program_id'      => ['nullable', 'integer', 'exists:programs,id'],
        ]);
        // ░░░ END: Validate Core Fields (HR) ░░░

        // ░░░ START: Interpret Role Requests ░░░
        $wantsDept = (bool) $request->boolean('request_dept_chair');
        $wantsVcaa = (bool) $request->boolean('request_vcaa');
        $wantsAssocVcaa = (bool) $request->boolean('request_assoc_vcaa');
        $wantsDean = (bool) $request->boolean('request_dean');
        // ░░░ END: Interpret Role Requests ░░░

        // ░░░ START: Conditional Validation for Scope ░░░
        if ($wantsDept && !$request->filled('department_id')) {
            return back()
                ->withErrors(['department_id' => 'Please select a department for the Department Chair request.'])
                ->withInput();
        }

        // Require department only when requesting Dean (only Dean is department-scoped)
        if ($wantsDean && !$request->filled('department_id')) {
            return back()
                ->withErrors(['department_id' => 'Please select your department for the Dean role request.'])
                ->withInput();
        }

        // Program Chair removed — no program-level requests processed server-side.
        // ░░░ END: Conditional Validation for Scope ░░░

        // ░░░ START: Save & Create Requests (Transaction) ░░░
    DB::transaction(function () use ($user, $request, $wantsDept, $wantsVcaa, $wantsAssocVcaa, $wantsDean) {
            // -- Save profile and HR fields on the user
            $userPayload = [
                'name'          => $request->input('name'),
                'email'         => $request->input('email'),
                'designation'   => $request->input('designation'),
                'employee_code' => $request->input('employee_code'),
            ];

            // If the user requested Dean and selected a department, persist that department on the user record.
            if ($wantsDean && $request->filled('department_id')) {
                $userPayload['department_id'] = (int) $request->input('department_id');
            }

            $user->update($userPayload);

            // -- Create Dept/Program Chair request (dedupe pending)
            if ($wantsDept) {
                $deptId = (int) $request->input('department_id');

                // Count programs in the selected department. If there's only one program, the
                // user should be requesting a Program Chair (PROG_CHAIR) instead of a Department Chair.
                $programCount = Program::where('department_id', $deptId)->count();

                if ($programCount === 1) {
                    // Find the single program id
                    $singleProgramId = Program::where('department_id', $deptId)->value('id');
                    ChairRequest::firstOrCreate(
                        [
                            'user_id'        => $user->id,
                            'requested_role' => ChairRequest::ROLE_PROG,
                            'department_id'  => $deptId,
                            'program_id'     => $singleProgramId,
                            'status'         => ChairRequest::STATUS_PENDING,
                        ],
                        []
                    );
                } else {
                    ChairRequest::firstOrCreate(
                        [
                            'user_id'        => $user->id,
                            'requested_role' => ChairRequest::ROLE_DEPT,
                            'department_id'  => $deptId,
                            'program_id'     => null,
                            'status'         => ChairRequest::STATUS_PENDING,
                        ],
                        [] // no extra defaults
                    );
                }
            }

            // Program Chair removed — no program-level requests created.

            // -- Create institution-level requests.
            // Dean is department-scoped; VCAA and Associate VCAA are institution-wide (no specific department).
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
        });
        // ░░░ END: Save & Create Requests (Transaction) ░░░

// ░░░ START: Response ░░░
Auth::logout(); // Kick them back to login until Superadmin approves

return redirect()
    ->route('admin.login.form')
    ->withErrors([
        'approval' => 'Your profile was submitted. Your account is pending approval by the Super Admin.',
    ]);
// ░░░ END: Response ░░░
    }
}
