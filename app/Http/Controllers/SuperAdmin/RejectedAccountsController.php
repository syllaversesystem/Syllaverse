<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class RejectedAccountsController extends Controller
{
    /** Show Rejected Accounts module. */
    public function index(): View
    {
        $rejectedAdmins = User::where('role', 'admin')
            ->where('status', 'rejected')
            ->orderBy('name')
            ->get();

        $rejectedFaculty = User::where('role', 'faculty')
            ->where('status', 'rejected')
            ->orderBy('name')
            ->get();

        return view('superadmin.rejected-accounts.index', [
            'rejectedAdmins' => $rejectedAdmins,
            'rejectedFaculty' => $rejectedFaculty,
        ]);
    }
}
