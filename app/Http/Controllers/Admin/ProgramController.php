<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/ProgramController.php
// * Description: Handles create, update, and delete of Programs (Admin - Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Added AJAX support: returns JSON when expectsJson().
// [2025-08-18] Synced with MasterDataController – delete now returns ID, consistent payloads.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Program;

class ProgramController extends Controller
{
    /**
     * Display the programs management page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filter parameter for department
        $departmentFilter = $request->get('department_filter');
        
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
        
        // Separate logic for add vs edit modals
        $showAddDepartmentDropdown = $showDepartmentDropdown;
        $showEditDepartmentDropdown = $showDepartmentDropdown;
        
        // Filter-based dropdown logic: if a specific department is filtered, hide dropdown in ADD modal only
        if ($departmentFilter && $departmentFilter !== 'all') {
            $userDepartment = $departmentFilter;
            $showAddDepartmentDropdown = false;
            // Keep $showEditDepartmentDropdown = true for edit modal to allow department changes
        }
        
        return view('admin.programs.index', compact(
            'programs', 
            'departments',
            'userDepartment',
            'showDepartmentDropdown',
            'showAddDepartmentDropdown',
            'showEditDepartmentDropdown',
            'showDepartmentFilter',
            'departmentFilter'
        ));
    }

    /**
     * Store a new program or restore a deleted one.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if a deleted program with the same code exists
        $deletedProgram = Program::where('code', $request->code)
                                ->where('status', Program::STATUS_DELETED)
                                ->first();

        if ($deletedProgram) {
            // Restore the deleted program
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'code'        => 'required|string|max:25', // Remove unique validation for restoration
                'description' => 'nullable|string',
                'department_id' => 'required|exists:departments,id',
            ]);

            $deletedProgram->update([
                'name'          => $validated['name'],
                'description'   => $validated['description'] ?? null,
                'department_id' => $validated['department_id'],
                'status'        => Program::STATUS_ACTIVE,
                'created_by'    => $user->id, // Update creator
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Program restored successfully!',
                    'program' => $deletedProgram->fresh()->load('department'),
                ]);
            }

            return redirect()->route('admin.master-data.index', [
                'tab' => 'programcourse',
                'subtab' => 'programs',
            ])->with('success', 'Program restored successfully!');
        }

        // Create new program (validate unique code among non-deleted programs)
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => [
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
            ],
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $program = Program::create([
            'name'          => $validated['name'],
            'code'          => $validated['code'],
            'description'   => $validated['description'] ?? null,
            'department_id' => $validated['department_id'],
            'created_by'    => $user->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Program added successfully!',
                'program' => $program->load('department'),
            ]);
        }

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'programs',
        ])->with('success', 'Program added successfully!');
    }

    /**
     * Update an existing program.
     */
    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $validated = $request->validate([
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
            'department_id' => 'required|exists:departments,id',
        ]);

        $program->update([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Program updated successfully!',
                'program' => $program->fresh()->load('department'),
            ]);
        }

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'programs',
        ])->with('success', 'Program updated successfully!');
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

        return redirect()->route('admin.programs.index')->with('success', $message);
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
}
