<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use Illuminate\View\View;

class ApprovedAccountsController extends Controller
{
    /** Show Approved Accounts module (faculty-only). */
    public function index(): View
    {
        // Load approved (active) faculty and eager-load appointments (no admin accounts here)
        // Note: elsewhere in the app, "approved" is represented by status = 'active'
        $approvedFaculty = User::where('role', 'faculty')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        if (method_exists($approvedFaculty, 'load')) {
            $approvedFaculty->load('appointments');
        }

        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();

        return view('superadmin.approved-accounts.index', [
            'approvedFaculty' => $approvedFaculty,
            'departments' => $departments,
            'programs' => $programs,
        ]);
    }
}
