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
        $programs = Program::orderBy('name')->get(['id', 'name', 'department_id']);
        // ░░░ END: Load Data for Form ░░░

        return view('admin.complete-profile', compact('user', 'departments', 'programs'));
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

        // ░░░ START: Validate Core Fields (HR) ░░░
        $request->validate([
            'designation'     => ['required', 'string', 'max:255'],
            'employee_code'   => ['required', 'string', 'max:255'],

            // Role-request inputs (optional)
            'request_dept_chair' => ['nullable', 'boolean'],
            'request_prog_chair' => ['nullable', 'boolean'],

            // Scope inputs (conditionally required in logic below)
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'program_id'      => ['nullable', 'integer', 'exists:programs,id'],
        ]);
        // ░░░ END: Validate Core Fields (HR) ░░░

        // ░░░ START: Interpret Role Requests ░░░
        $wantsDept = (bool) $request->boolean('request_dept_chair');
        $wantsProg = (bool) $request->boolean('request_prog_chair');
        // ░░░ END: Interpret Role Requests ░░░

        // ░░░ START: Conditional Validation for Scope ░░░
        if ($wantsDept && !$request->filled('department_id')) {
            return back()
                ->withErrors(['department_id' => 'Please select a department for the Department Chair request.'])
                ->withInput();
        }

        if ($wantsProg) {
            if (!$request->filled('department_id')) {
                return back()
                    ->withErrors(['department_id' => 'Please select a department to filter programs.'])
                    ->withInput();
            }
            if (!$request->filled('program_id')) {
                return back()
                    ->withErrors(['program_id' => 'Please select a program for the Program Chair request.'])
                    ->withInput();
            }

            // Guard: ensure chosen program belongs to chosen department.
            $program = Program::find($request->input('program_id'));
            if ($program && (int) $program->department_id !== (int) $request->input('department_id')) {
                return back()
                    ->withErrors(['program_id' => 'Selected program does not belong to the selected department.'])
                    ->withInput();
            }
        }
        // ░░░ END: Conditional Validation for Scope ░░░

        // ░░░ START: Save & Create Requests (Transaction) ░░░
        DB::transaction(function () use ($user, $request, $wantsDept, $wantsProg) {
            // -- Save HR fields on the user
            $user->update([
                'designation'   => $request->input('designation'),
                'employee_code' => $request->input('employee_code'),
            ]);

            // -- Create Dept Chair request (dedupe pending)
            if ($wantsDept) {
                ChairRequest::firstOrCreate(
                    [
                        'user_id'        => $user->id,
                        'requested_role' => ChairRequest::ROLE_DEPT,
                        'department_id'  => $request->input('department_id'),
                        'program_id'     => null,
                        'status'         => ChairRequest::STATUS_PENDING,
                    ],
                    [] // no extra defaults
                );
            }

            // -- Create Program Chair request (dedupe pending)
            if ($wantsProg) {
                ChairRequest::firstOrCreate(
                    [
                        'user_id'        => $user->id,
                        'requested_role' => ChairRequest::ROLE_PROG,
                        'department_id'  => $request->input('department_id'),
                        'program_id'     => $request->input('program_id'),
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
