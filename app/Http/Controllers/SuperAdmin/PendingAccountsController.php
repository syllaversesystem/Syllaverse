<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChairRequest;
use App\Models\Department;
use App\Models\Program;
use Illuminate\View\View;

class PendingAccountsController extends Controller
{
    /** Show Pending Accounts module (approvals-style data). */
    public function index(): View
    {
        // Match ManageAdminController pending filters: require completed profile fields
        $pendingAdmins = User::where('role', 'admin')
            ->where('status', 'pending')
            ->whereNotNull('designation')
            ->whereNotNull('employee_code')
            ->orderByDesc('created_at')
            ->get();

        $pendingFaculty = User::where('role', 'faculty')
            ->where('status', 'pending')
            ->whereNotNull('designation')
            ->whereNotNull('employee_code')
            ->orderByDesc('created_at')
            ->get();

        $pendingChairRequests = ChairRequest::with(['user', 'department', 'program'])
            ->where('status', ChairRequest::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get();

        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();

        return view('superadmin.pending-accounts.index', [
            'pendingAdmins' => $pendingAdmins,
            'pendingFaculty' => $pendingFaculty,
            'pendingChairRequests' => $pendingChairRequests,
            'departments' => $departments,
            'programs' => $programs,
        ]);
    }
}
