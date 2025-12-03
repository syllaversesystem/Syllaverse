<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\ChairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ManageProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('faculty')->user() ?? Auth::user();
        if (!$user) { return redirect()->route('faculty.login.form'); }
        // Fetch role requests for display
        $roleRequests = collect();
        $departments = collect();
        $activeAppointments = collect();
        try {
            $roleRequests = \Illuminate\Support\Facades\DB::table('role_requests')
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(25)
                ->get();
        } catch (\Throwable $e) { /* role_requests may not exist yet; ignore */ }
        // Fallback: If no local role_requests, derive from ChairRequest for status display
        if (!$roleRequests || $roleRequests->count() === 0) {
            try {
                $cr = \App\Models\ChairRequest::with(['department'])
                    ->where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->limit(25)
                    ->get();
                $roleRequests = $cr->map(function($r){
                    $map = [
                        \App\Models\ChairRequest::ROLE_DEAN => 'dean',
                        \App\Models\ChairRequest::ROLE_ASSOC_DEAN => 'assoc_dean',
                        \App\Models\ChairRequest::ROLE_DEPT => 'dept_chair',
                        \App\Models\ChairRequest::ROLE_DEPT_HEAD => 'dept_chair',
                        \App\Models\ChairRequest::ROLE_FACULTY => 'faculty',
                    ];
                    return (object) [
                        'id' => $r->id,
                        'role' => $map[$r->requested_role] ?? strtolower($r->requested_role),
                        'department_id' => $r->department_id,
                        'status' => $r->status,
                        'created_at' => $r->created_at,
                    ];
                });
            } catch (\Throwable $e) { /* if ChairRequest missing, leave empty */ }
        }
        try {
            $departments = \Illuminate\Support\Facades\DB::table('departments')->select('id','name')->get();
        } catch (\Throwable $e) { /* departments table should exist; if not, leave empty */ }
        try {
            $activeAppointments = \Illuminate\Support\Facades\DB::table('appointments')
                ->select('id','role','scope_id','status')
                ->where('user_id', $user->id)
                ->where('status','active')
                ->get();
        } catch (\Throwable $e) { /* appointments table may not exist; ignore */ }
        return view('faculty.manage-profile.index', [ 'user' => $user, 'roleRequests' => $roleRequests, 'departments' => $departments, 'activeAppointments' => $activeAppointments ]);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('faculty')->user() ?? Auth::user();
        if (!$user) { return response()->json(['message'=>'Unauthorized'], 401); }
        $data = $request->only(['name','employee_code','designation']);
        $v = Validator::make($data, [
            'name' => ['required','string','max:255'],
            'employee_code' => ['nullable','string','max:64'],
            'designation' => ['nullable','string','max:255'],
        ]);
        if ($v->fails()) {
            return response()->json(['errors'=>$v->errors()], 422);
        }
        try {
            DB::transaction(function() use ($user, $data) {
                $user->name = $data['name'];
                $user->employee_code = $data['employee_code'] ?? $user->employee_code;
                $user->designation = $data['designation'] ?? $user->designation;
                $user->save();
            });
            return response()->json(['message'=>'Profile updated']);
        } catch (\Throwable $e) {
            Log::error('ManageProfile update failed', ['err'=>$e]);
            return response()->json(['message'=>'Server error'], 500);
        }
    }

    public function requestRole(Request $request)
    {
        $user = Auth::guard('faculty')->user() ?? Auth::user();
        if (!$user) { return response()->json(['message'=>'Unauthorized'], 401); }
        $allowed = ['dean','dept_chair','assoc_dean','faculty'];
        $roles = $request->input('roles');
        // Backward compatibility with single role payload
        if (!$roles) {
            $singleRole = (string) $request->input('role');
            if ($singleRole) { $roles = [$singleRole]; }
        }
        if (!is_array($roles) || !count($roles)) {
            return response()->json(['message'=>'No roles selected'], 422);
        }
        $deptMap = (array) $request->input('department_id', []);
        // Validate roles and departments
        foreach ($roles as $r) {
            if (!in_array($r, $allowed, true)) {
                return response()->json(['message'=>"Invalid role: $r"], 422);
            }
            if (in_array($r, ['dean','assoc_dean','dept_chair'], true)) {
                $rid = isset($deptMap[$r]) ? (int) $deptMap[$r] : 0;
                if (!$rid) return response()->json(['message'=>"Department is required for $r"], 422);
            }
            if ($r === 'faculty') {
                $rid = isset($deptMap[$r]) ? (int) $deptMap[$r] : 0;
                if (!$rid) return response()->json(['message'=>'Department is required for faculty'], 422);
            }
        }

        try {
            DB::transaction(function() use ($user, $roles, $deptMap) {
                // Canonical ChairRequest roles per specification
                $roleMap = [
                    // Department Head (Dean/Head/Principal)
                    'dean' => ChairRequest::ROLE_DEPT_HEAD,
                    // Associate Dean
                    'assoc_dean' => ChairRequest::ROLE_ASSOC_DEAN,
                    // Chairperson
                    'dept_chair' => ChairRequest::ROLE_CHAIR,
                    // Faculty
                    'faculty' => ChairRequest::ROLE_FACULTY,
                ];

                        foreach ($roles as $r) {
                    $deptId = isset($deptMap[$r]) ? (int) $deptMap[$r] : null;
                    // Local role_requests record for quick UI status (best-effort; table may not exist)
                    try {
                        DB::table('role_requests')->insert([
                            'user_id' => $user->id,
                            'role' => $r,
                            'department_id' => $deptId,
                            'status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        // Ignore if the lightweight UI table isn't available yet
                    }

                            // Create ChairRequest with resubmission rules
                    $crRole = $roleMap[$r] ?? null;
                    if ($crRole) {
                                $baseQuery = ChairRequest::where([
                                    'user_id'        => $user->id,
                                    'requested_role' => $crRole,
                                    'department_id'  => $deptId,
                                    'program_id'     => null,
                                ]);
                                $hasPending  = (clone $baseQuery)->where('status', ChairRequest::STATUS_PENDING)->exists();
                                $hasApproved = (clone $baseQuery)->where('status', ChairRequest::STATUS_APPROVED)->exists();
                                // Allow new submission if none pending and none approved (i.e., new or previously rejected)
                                if (!$hasPending && !$hasApproved) {
                                    ChairRequest::create([
                                        'user_id'        => $user->id,
                                        'requested_role' => $crRole,
                                        'department_id'  => $deptId,
                                        'program_id'     => null,
                                        'status'         => ChairRequest::STATUS_PENDING,
                                    ]);
                                }
                    }
                }

                        // No auto-create of Faculty for leadership selections (dean/assoc_dean/dept_chair)
            });
        } catch (\Throwable $e) {
            Log::error('ManageProfile requestRole failed', ['err'=>$e]);
            return response()->json(['message'=>'Server error'], 500);
        }
        return response()->json(['message'=>'Role request submitted']);
    }

    public function destroy(Request $request)
    {
        $user = Auth::guard('faculty')->user() ?? Auth::user();
        if (!$user) { return response()->json(['message'=>'Unauthorized'], 401); }
        try {
            DB::transaction(function() use ($user) {
                // Soft delete preferred; fallback hard delete if model doesn't support
                if (method_exists($user, 'delete')) {
                    $user->delete();
                } else {
                    DB::table('users')->where('id', $user->id)->delete();
                }
            });
            Auth::guard('faculty')->logout();
            return response()->json(['message'=>'Account deleted']);
        } catch (\Throwable $e) {
            Log::error('ManageProfile destroy failed', ['err'=>$e]);
            return response()->json(['message'=>'Server error'], 500);
        }
    }
}
