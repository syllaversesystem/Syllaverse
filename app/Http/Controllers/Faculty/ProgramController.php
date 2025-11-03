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
        
        // Check if user has any administrative roles
        $hasAdministrativeRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']);
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
        
        // Get filter parameter for department
        $departmentFilter = $request->get('department');
        
        // Build programs query with optional department filter
        $programsQuery = Program::with(['department'])->notDeleted();
        if ($departmentFilter && $departmentFilter !== 'all') {
            $programsQuery->where('department_id', $departmentFilter);
        }
        $programs = $programsQuery->get();
        
        // Get all departments for dropdowns
        $departments = \App\Models\Department::all();
        
        // Check user roles and determine department dropdown visibility
        $userDepartment = null;
        $showDepartmentDropdown = true;
        
        // Get all active appointments for the user
        $userAppointments = $user->appointments()->active()->get();
        
        // Check for institution-wide roles (roles with Institution scope)
        $hasInstitutionWideRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']) || 
                   ($appointment->scope_type === 'Institution') ||
                   ($appointment->role === 'DEAN' && $appointment->scope_type === 'Institution');
        });
        
        // Check specifically for VCAA/ASSOC_VCAA roles for department filter
        $showDepartmentFilter = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
        });
        
        // Check if user has any administrative roles (to show department column)
        $hasAdministrativeRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']);
        });
        
        // Hide department column for users without institution-wide scope (only VCAA/ASSOC_VCAA can see department column)
        $showDepartmentColumn = $hasInstitutionWideRole;
        
        // Check for department-specific roles
        $departmentSpecificAppointments = $userAppointments->filter(function($appointment) {
            return in_array($appointment->role, ['DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']) && 
                   $appointment->scope_type === 'Department' && 
                   !empty($appointment->scope_id);
        });
        
        // Role-based dropdown logic
        if ($departmentSpecificAppointments->isNotEmpty() && !$hasInstitutionWideRole) {
            // User has ONLY department-specific roles - restrict to their department
            $firstDeptAppointment = $departmentSpecificAppointments->first();
            $userDepartment = $firstDeptAppointment->scope_id;
            $showDepartmentDropdown = false;
        }
        
        // For modals: hide department dropdown if user doesn't have VCAA/Associate VCAA role
        // Only VCAA and Associate VCAA users can see department selection in program modals
        $hasVcaaRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
        });
        $showAddDepartmentDropdown = $hasVcaaRole;
        $showEditDepartmentDropdown = $hasVcaaRole;
        
        // If user has no administrative role, get their department from scope
        if (!$hasAdministrativeRole) {
            $facultyAppointment = $userAppointments->filter(function($appointment) {
                return $appointment->role === 'FACULTY' && 
                       $appointment->scope_type === 'Department' && 
                       !empty($appointment->scope_id);
            })->first();
            
            if ($facultyAppointment) {
                $userDepartment = $facultyAppointment->scope_id;
            }
        }
        
        // Filter-based dropdown logic: if a specific department is filtered, pre-select it but keep dropdown pickable
        if ($departmentFilter && $departmentFilter !== 'all') {
            $userDepartment = $departmentFilter;
            // Keep both add and edit dropdowns visible and pickable, but pre-select the filtered department
        }
        
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
        
        // Build programs query with optional department filter
        $programsQuery = Program::with(['department'])->notDeleted();
        if ($departmentFilter && $departmentFilter !== 'all') {
            $programsQuery->where('department_id', $departmentFilter);
        }
        $programs = $programsQuery->get();
        
        // Get all departments for context
        $departments = \App\Models\Department::all();
        
        // Get user permissions (same logic as index method)
        $userAppointments = $user->appointments()->active()->get();
        
        // Check for institution-wide roles (roles with Institution scope)
        $hasInstitutionWideRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']) || 
                   ($appointment->scope_type === 'Institution') ||
                   ($appointment->role === 'DEAN' && $appointment->scope_type === 'Institution');
        });
        
        // Check if user has any administrative roles (to show department column)
        $hasAdministrativeRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']);
        });
        
        // Hide department column for users without institution-wide scope (only VCAA/ASSOC_VCAA can see department column)
        $showDepartmentColumn = $hasInstitutionWideRole;
        
        // Return JSON response with table data
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('faculty.programs.partials.programs-table-content', compact(
                    'programs',
                    'departments', 
                    'showDepartmentColumn',
                    'departmentFilter'
                ))->render(),
                'count' => $programs->count(),
                'department_filter' => $departmentFilter
            ]);
        }
        
        // Fallback to redirect for non-AJAX requests
        return redirect()->route('faculty.programs.index', ['department' => $departmentFilter]);
    }
}