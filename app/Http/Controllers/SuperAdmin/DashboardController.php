<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

// Models
use App\Models\Department;
use App\Models\Program;
use App\Models\Course;
use App\Models\User;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index()
    {
        // Lightweight caching to avoid repeated heavy counts
        $stats = Cache::remember('superadmin_dashboard_stats', 60, function () {

            return [
                // Entities
                'departments' => Department::count(),
                'programs' => Program::count(),
                'courses' => Course::count(),

                // Faculty accounts (users with role FACULTY)
                'faculty' => User::where('role', 'FACULTY')->count(),

                // Pending accounts (admins + faculty with completed profile fields)
                'pending_accounts' => User::whereIn('role', ['admin', 'faculty'])
                    ->where('status', 'pending')
                    ->whereNotNull('designation')
                    ->whereNotNull('employee_code')
                    ->count(),
            ];
        });

        // Leadership directory: flattened rows of Name, Role, Department (cached)
        $leadership = Cache::remember('superadmin_dashboard_leadership', 60, function () {
            $departments = Department::select('id', 'name', 'code')->orderBy('name')->get();
            $deptIds = $departments->pluck('id');

            $activeAppts = Appointment::whereIn('scope_id', $deptIds)
                ->where('status', 'active')
                ->whereIn('role', ['DEPT_HEAD', 'CHAIR', 'ASSOC_DEAN'])
                ->with(['user:id,name'])
                ->orderBy('role')
                ->get()
                ->groupBy('scope_id');

            $rows = collect();
            foreach ($departments as $dept) {
                $appts = $activeAppts->get($dept->id) ?? collect();
                foreach ($appts as $appt) {
                    $label = match ($appt->role) {
                        'DEPT_HEAD' => 'Dept Head',
                        'CHAIR' => 'Chair',
                        'ASSOC_DEAN' => 'Assoc Dean',
                        default => $appt->role,
                    };
                    $rows->push([
                        'name' => optional($appt->user)->name,
                        'role' => $label,
                        'department' => $dept->code,
                        'department_id' => $dept->id,
                    ]);
                }
            }
            // Sort by department code then role
            return $rows->sortBy(['department', 'role'])->values();
        });

        // Accounts by department: labels (dept code) + counts
        $accountsByDept = Cache::remember('superadmin_dashboard_accounts_by_dept', 60, function () {
            // Count distinct users with active appointments grouped by department (scope_id)
            $departments = Department::select('id', 'code')->get()->keyBy('id');
            $counts = Appointment::selectRaw('scope_id, COUNT(DISTINCT user_id) AS total')
                ->where('status', 'active')
                ->whereNotNull('scope_id')
                ->groupBy('scope_id')
                ->orderByDesc('total')
                ->get()
                ->map(function ($row) use ($departments) {
                    $code = optional($departments->get($row->scope_id))->code ?? 'N/A';
                    return ['department' => $code, 'total' => (int) $row->total];
                })
                ->values();
            return $counts;
        });

        // Syllabus status by department (aggregate by explicit submission_status values)
        $syllabusStatusByDept = Cache::remember('superadmin_dashboard_syllabus_status_by_dept_v4', 60, function () {
            // Aggregate syllabi by department via course.department_id and submission_status
            $departments = Department::select('id', 'code')->get()->keyBy('id');
            $rows = \DB::table('syllabi')
                ->join('courses', 'syllabi.course_id', '=', 'courses.id')
                ->select('courses.department_id as dept_id', 'syllabi.submission_status', \DB::raw('COUNT(*) as total'))
                ->groupBy('dept_id', 'syllabi.submission_status')
                ->get();

            // Map strictly per provided values:
            // - draft: submission_status = 'draft'
            // - pending: submission_status = 'pending_review'
            // - reviewed: submission_status = 'approved'
            // Also track final_approved for the Status column (submission_status = 'final_approved')
            $bucket = [];
            foreach ($rows as $r) {
                $deptId = $r->dept_id ?: 0;
                $code = optional($departments->get($deptId))->code ?? 'N/A';
                if (!isset($bucket[$deptId])) {
                    $bucket[$deptId] = [
                        'department' => $code,
                        'draft' => 0,
                        'pending' => 0,
                        'reviewed' => 0,
                        'final_approved' => 0,
                    ];
                }

                $status = strtolower($r->submission_status ?? '');
                if ($status === 'draft') {
                    $bucket[$deptId]['draft'] += (int) $r->total;
                } elseif ($status === 'pending_review') {
                    $bucket[$deptId]['pending'] += (int) $r->total;
                } elseif ($status === 'approved') {
                    $bucket[$deptId]['reviewed'] += (int) $r->total;
                } elseif ($status === 'final_approved') {
                    $bucket[$deptId]['final_approved'] += (int) $r->total;
                } else {
                    // Anything else: treat as draft
                    $bucket[$deptId]['draft'] += (int) $r->total;
                }
            }

            // Compute overall status label and reviewed percentage
            $result = collect();
            foreach ($bucket as $deptId => $vals) {
                $total = (int) ($vals['draft'] + $vals['pending'] + $vals['reviewed']);
                $safeTotal = max($total, 1);
                $pct = (int) floor(($vals['reviewed'] / $safeTotal) * 100);
                // Status column requirement: reflect count of final_approved items
                // Also keep a human-readable label for optional UI use
                $statusLabel = $pct >= 75 ? 'Good' : ($pct >= 40 ? 'Progressing' : 'Behind');

                $result->push([
                    'department' => $vals['department'],
                    'draft' => $vals['draft'],
                    'pending' => $vals['pending'],
                    'reviewed' => $vals['reviewed'],
                    'final_approved' => $vals['final_approved'],
                    'total' => $total,
                    'reviewed_pct' => $pct,
                    // Status = count of final_approved (per spec)
                    'status' => $vals['final_approved'],
                    // Optional label retained
                    'status_label' => $statusLabel,
                ]);
            }

            // Sort by department code
            return $result->sortBy('department')->values();
        });

        return view('superadmin.dashboard.index', [
            'stats' => $stats,
            'leadership' => $leadership,
            'accountsByDept' => $accountsByDept,
            'syllabusStatusByDept' => $syllabusStatusByDept,
        ]);
    }
}
