<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/ProgramController.php
// * Description: Handles create, update, and delete of Programs (Faculty - Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-01-16] Created faculty version based on admin ProgramController
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Program;

class ProgramController extends Controller
{
    /**
     * Get the user's department ID based on their role
     * For basic faculty users, return their faculty appointment department
     * For administrative users, return null to allow department selection
     */
    private function getUserDepartmentId($user)
    {
        // Get all active appointments for the user
        $userAppointments = $user->appointments()->active()->get();
        
        // Log for debugging
        \Log::info('User appointments for ' . $user->id, $userAppointments->toArray());
        
        // Check if user has any administrative roles (treat DEPT_HEAD same as DEPT_CHAIR)
        $hasAdministrativeRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'DEPT_HEAD', 'PROG_CHAIR']);
        });
        
        \Log::info('Has administrative role: ' . ($hasAdministrativeRole ? 'true' : 'false'));
        
        // If user has administrative role, allow them to choose department
        if ($hasAdministrativeRole) {
            return null;
        }
        
        // For basic faculty users, get their department from faculty appointment
        $facultyAppointment = $userAppointments->filter(function($appointment) {
            return $appointment->role === 'FACULTY' && 
                   $appointment->scope_type === 'Department' && 
                   !empty($appointment->scope_id);
        })->first();
        
        $departmentId = $facultyAppointment ? $facultyAppointment->scope_id : null;
        \Log::info('Faculty department ID: ' . $departmentId);
        
        return $departmentId;
    }

    /**
     * Display the programs management page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Active appointments for scoping logic
        $userAppointments = $user->appointments()->active()->get();

        // Institution-wide roles (see ALL departments)
        $hasInstitutionWideRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']) ||
                   ($appointment->scope_type === 'Institution') ||
                   ($appointment->role === 'DEAN' && $appointment->scope_type === 'Institution');
        });

        // Department filter honored only for institution-wide users
        $departmentFilter = $hasInstitutionWideRole ? $request->get('department') : null;

        // Resolve a single scoped department for non institution-wide users
        $scopedDepartmentId = null;
        if (!$hasInstitutionWideRole) {
            // 1. Department leadership (DEPT_HEAD / DEPT_CHAIR / DEAN with Department scope)
            $deptAppt = $userAppointments->first(function($a) {
                return in_array($a->role, ['DEPT_HEAD', 'DEPT_CHAIR', 'DEAN']) &&
                       $a->scope_type === 'Department' && !empty($a->scope_id);
            });
            if ($deptAppt) { $scopedDepartmentId = (int) $deptAppt->scope_id; }

            // 2. Program Chair -> map program to department
            if (!$scopedDepartmentId) {
                $progAppt = $userAppointments->first(function($a) {
                    return $a->role === 'PROG_CHAIR' && $a->scope_type === 'Program' && !empty($a->scope_id);
                });
                if ($progAppt) {
                    $progDept = Program::where('id', $progAppt->scope_id)->value('department_id');
                    if ($progDept) { $scopedDepartmentId = (int) $progDept; }
                }
            }

            // 3. Faculty department appointment fallback
            if (!$scopedDepartmentId) {
                $facultyAppt = $userAppointments->first(function($a) {
                    return $a->role === 'FACULTY' && $a->scope_type === 'Department' && !empty($a->scope_id);
                });
                if ($facultyAppt) { $scopedDepartmentId = (int) $facultyAppt->scope_id; }
            }
        }

        // Build programs query with enforced scoping
        $programsQuery = Program::with(['department'])->notDeleted();
        if ($hasInstitutionWideRole) {
            if ($departmentFilter && $departmentFilter !== 'all') {
                $programsQuery->where('department_id', $departmentFilter);
            }
        } elseif ($scopedDepartmentId) {
            $programsQuery->where('department_id', $scopedDepartmentId);
        }
        $programs = $programsQuery->get();

        // Departments list for dropdown (still load full list; UI hides where needed)
        $departments = \App\Models\Department::all();

        // UI flags
        $showDepartmentFilter = $hasInstitutionWideRole; // Only institution-wide users see filter
        $showDepartmentColumn = $hasInstitutionWideRole; // Hide column for scoped users
        $hasVcaaRole = $userAppointments->contains(function($a) { return in_array($a->role, ['VCAA', 'ASSOC_VCAA']); });
        $showAddDepartmentDropdown  = $hasVcaaRole;
        $showEditDepartmentDropdown = $hasVcaaRole;
        $userDepartment = $scopedDepartmentId; // implicit department for scoped users
        $showDepartmentDropdown = $hasInstitutionWideRole; // legacy variable

        return view('faculty.programs.index', compact(
            'programs',
            'departments',
            'userDepartment',
            'showDepartmentDropdown',
            'showAddDepartmentDropdown',
            'showEditDepartmentDropdown',
            'showDepartmentFilter',
            'showDepartmentColumn',
            'departmentFilter'
        ));
    }

    /**
     * Store a new program or restore a deleted one.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userDepartmentId = $this->getUserDepartmentId($user);

        // Log for debugging
        \Log::info('Store method - userDepartmentId: ' . $userDepartmentId);
        \Log::info('Store method - request department_id: ' . $request->input('department_id'));

        // Determine the department ID to use
        $departmentId = $userDepartmentId ?? $request->input('department_id');

        // Check for program restoration
        $restoreProgramId = $request->input('restore_program_id');
        $programToRestore = null;
        
        if ($restoreProgramId) {
            $programToRestore = Program::where('id', $restoreProgramId)
                                      ->where('status', Program::STATUS_DELETED)
                                      ->first();
        }

        // If no restore ID provided, check if a deleted program with the same code exists
        if (!$programToRestore) {
            $programToRestore = Program::where('code', $request->input('code'))
                                      ->where('status', Program::STATUS_DELETED)
                                      ->first();
        }

        // Build validation rules dynamically
        $validationRules = [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ];

        // Add code validation (different rules for new vs restore)
        if ($programToRestore) {
            // For restore, just validate code format (no uniqueness check)
            $validationRules['code'] = 'required|string|max:25';
        } else {
            // For new programs, validate uniqueness
            $validationRules['code'] = [
                'required',
                'string',
                'max:25',
                function ($attribute, $value, $fail) {
                    $exists = Program::where('code', $value)
                                    ->whereIn('status', [Program::STATUS_ACTIVE, Program::STATUS_INACTIVE])
                                    ->exists();
                    if ($exists) {
                        $fail('The program code has already been taken.');
                    }
                }
            ];
        }

        // Only add department_id validation if user doesn't have auto-assigned department
        if (!$userDepartmentId) {
            $validationRules['department_id'] = 'required|exists:departments,id';
        }

        $validated = $request->validate($validationRules);

        if ($programToRestore) {
            // Restore the deleted or removed program
            $programToRestore->update([
                'name'          => $validated['name'],
                'description'   => $validated['description'] ?? null,
                'department_id' => $departmentId,
                'status'        => Program::STATUS_ACTIVE,
                'created_by'    => $user->id, // Update creator
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Program restored successfully!',
                    'program' => $programToRestore->fresh()->load('department'),
                ]);
            }

            return redirect()->route('faculty.programs.index')->with('success', 'Program restored successfully!');
        }

        // Ensure we have a valid department ID
        if (!$departmentId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to determine department for program creation.',
                    'errors' => ['department_id' => ['Department is required but could not be determined from your role.']]
                ], 422);
            }
            return redirect()->back()->withErrors(['department_id' => 'Department is required but could not be determined from your role.']);
        }

        $program = Program::create([
            'name'          => $validated['name'],
            'code'          => $validated['code'],
            'description'   => $validated['description'] ?? null,
            'department_id' => $departmentId,
            'created_by'    => $user->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Program added successfully!',
                'program' => $program->load('department'),
            ]);
        }

        return redirect()->route('faculty.programs.index')->with('success', 'Program added successfully!');
    }

    /**
     * Update an existing program.
     */
    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);
        $user = Auth::user();
        $userDepartmentId = $this->getUserDepartmentId($user);

        // Determine the department ID to use
        $departmentId = $userDepartmentId ?? $request->input('department_id');

        // Build validation rules dynamically
        $validationRules = [
            'name'        => 'required|string|max:255',
            'code'        => [
                'required',
                'string',
                'max:25',
                function ($attribute, $value, $fail) use ($program) {
                    $exists = Program::where('code', $value)
                                    ->where('id', '!=', $program->id)
                                    ->whereIn('status', [Program::STATUS_ACTIVE, Program::STATUS_INACTIVE])
                                    ->exists();
                    if ($exists) {
                        $fail('The program code has already been taken.');
                    }
                }
            ],
            'description' => 'nullable|string',
        ];

        // Only add department_id validation if user doesn't have auto-assigned department
        if (!$userDepartmentId) {
            $validationRules['department_id'] = 'required|exists:departments,id';
        }

        $validated = $request->validate($validationRules);

        // Ensure we have a valid department ID
        if (!$departmentId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to determine department for program update.',
                    'errors' => ['department_id' => ['Department is required but could not be determined from your role.']]
                ], 422);
            }
            return redirect()->back()->withErrors(['department_id' => 'Department is required but could not be determined from your role.']);
        }

        $program->update([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'description' => $validated['description'] ?? null,
            'department_id' => $departmentId,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Program updated successfully!',
                'program' => $program->fresh()->load('department'),
            ]);
        }

        return redirect()->route('faculty.programs.index')->with('success', 'Program updated successfully!');
    }

    /**
     * Remove or delete a program based on action_type.
     */
    public function destroy(Request $request, $id)
    {
        $program = Program::findOrFail($id);
        
        // Validate action type
        $request->validate([
            'action_type' => 'required|in:remove,delete'
        ]);
        
        $actionType = $request->input('action_type');
        $message = '';
        
        if ($actionType === 'remove') {
            // Set status to deleted (soft removal)
            $program->update(['status' => Program::STATUS_DELETED]);
            $message = 'Program removed successfully! It can be restored later if needed.';
        } else {
            // Permanent deletion
            $program->delete();
            $message = 'Program deleted permanently!';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'id'      => $id,
                'action'  => $actionType,
            ]);
        }

        return redirect()->route('faculty.programs.index')->with('success', $message);
    }

    /**
     * Search for deleted programs based on name or code.
     */
    public function searchDeleted(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $deletedPrograms = Program::where('status', Program::STATUS_DELETED)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('code', 'LIKE', "%{$query}%");
            })
            ->with('department')
            ->limit(5)
            ->get();

        return response()->json($deletedPrograms->map(function($program) {
            return [
                'id' => $program->id,
                'name' => $program->name,
                'code' => $program->code,
                'description' => $program->description,
                'department_id' => $program->department_id,
                'department_name' => $program->department->name ?? 'Unknown Department',
                'display_text' => "{$program->name} ({$program->code}) - {$program->department->name}",
            ];
        }));
    }

    /**
     * Search for removed programs based on name or code for restoration.
     */
    public function searchRemoved(Request $request)
    {
        $query = $request->get('query', '');
        $field = $request->get('field', 'name');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Search for programs with status 'deleted' (removed programs)
        $removedPrograms = Program::where('status', Program::STATUS_DELETED)
            ->where(function($q) use ($query, $field) {
                if ($field === 'code') {
                    $q->where('code', 'LIKE', "%{$query}%");
                } else {
                    $q->where('name', 'LIKE', "%{$query}%");
                }
            })
            ->with('department')
            ->limit(10)
            ->get();

        return response()->json($removedPrograms->map(function($program) {
            return [
                'id' => $program->id,
                'name' => $program->name,
                'code' => $program->code,
                'description' => $program->description,
                'department_id' => $program->department_id,
                'department_name' => $program->department->name ?? 'Unknown Department',
            ];
        }));
    }

    /**
     * Filter programs by department via AJAX.
     */
    public function filterByDepartment(Request $request)
    {
        $user = auth()->user();
        $departmentFilter = $request->get('department');
        $q = trim((string) $request->get('q', ''));
        
        // Appointments and institution-wide evaluation
        $userAppointments = $user->appointments()->active()->get();
        $hasInstitutionWideRole = $userAppointments->contains(function($a) {
            return in_array($a->role, ['VCAA', 'ASSOC_VCAA']) ||
                   ($a->scope_type === 'Institution') ||
                   ($a->role === 'DEAN' && $a->scope_type === 'Institution');
        });

        // Resolve scoped department for non institution-wide users
        $scopedDeptId = null;
        if (!$hasInstitutionWideRole) {
            $deptAppt = $userAppointments->first(function($a) {
                return in_array($a->role, ['DEPT_HEAD', 'DEPT_CHAIR', 'DEAN']) && $a->scope_type === 'Department' && !empty($a->scope_id);
            });
            if ($deptAppt) { $scopedDeptId = (int) $deptAppt->scope_id; }
            if (!$scopedDeptId) {
                $progAppt = $userAppointments->first(function($a) {
                    return $a->role === 'PROG_CHAIR' && $a->scope_type === 'Program' && !empty($a->scope_id);
                });
                if ($progAppt) {
                    $progDept = Program::where('id', $progAppt->scope_id)->value('department_id');
                    if ($progDept) { $scopedDeptId = (int) $progDept; }
                }
            }
            if (!$scopedDeptId) {
                $facultyAppt = $userAppointments->first(function($a) {
                    return $a->role === 'FACULTY' && $a->scope_type === 'Department' && !empty($a->scope_id);
                });
                if ($facultyAppt) { $scopedDeptId = (int) $facultyAppt->scope_id; }
            }
        }

        // Build query with enforced scoping
        $programsQuery = Program::with(['department'])->notDeleted();
        if ($hasInstitutionWideRole) {
            if ($departmentFilter && $departmentFilter !== 'all') {
                $programsQuery->where('department_id', $departmentFilter);
            }
        } elseif ($scopedDeptId) {
            $programsQuery->where('department_id', $scopedDeptId);
        }
        if ($q !== '') {
            $programsQuery->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }
        $programs = $programsQuery->get();
        $isSearch = $q !== '';

        $departments = \App\Models\Department::all();
        $showDepartmentColumn = $hasInstitutionWideRole;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('faculty.programs.partials.programs-table-content', compact(
                    'programs',
                    'departments',
                    'showDepartmentColumn',
                    'departmentFilter',
                    'isSearch'
                ))->render(),
                'count' => $programs->count(),
                'department_filter' => $hasInstitutionWideRole ? $departmentFilter : $scopedDeptId,
                'search' => $q,
            ]);
        }

        return redirect()->route('faculty.programs.index');
    }
}