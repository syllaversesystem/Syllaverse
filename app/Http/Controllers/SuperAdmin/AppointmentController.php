<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/AppointmentController.php
// * Description: Create/Update/End/Destroy chair appointments – JSON payload for AJAX DOM updates
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-11] AJAX – JSON responses for XHR; redirect+flash for normal posts.
// [2025-08-11] Robust – tolerant role parsing (dept/prog variants) + one-active-per-scope guard.
// [2025-08-11] DOM – returns { admin_id, appointments[] } for client-side rendering (no Blade fragments).
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }

    /** Normalize various role inputs to model constants or null if unknown. */
    protected function normalizeRole(mixed $role): ?string
    {
        $r = strtoupper(trim((string) $role));
        
        // Direct matches
        if ($r === Appointment::ROLE_FACULTY) return $r;
        if ($r === Appointment::ROLE_DEPT) return $r;
        if ($r === Appointment::ROLE_DEPT_HEAD) return $r;
        if ($r === Appointment::ROLE_PROG) return $r;
        if ($r === Appointment::ROLE_DEAN) return $r;
        if ($r === Appointment::ROLE_ASSOC_DEAN) return $r;
        if ($r === Appointment::ROLE_VCAA) return $r;
        if ($r === Appointment::ROLE_ASSOC_VCAA) return $r;
        
        // Alternative input formats
        if (in_array($r, ['FACULTY'], true)) return Appointment::ROLE_FACULTY;
        if (in_array($r, ['DEPT','DEPARTMENT','DEPARTMENT_CHAIR','DEPT_HEAD','DEPARTMENT_HEAD','0'], true)) return Appointment::ROLE_DEPT_HEAD;
        if (in_array($r, ['PROG','PROGRAM','PROGRAM_CHAIR'], true)) return Appointment::ROLE_PROG;
        if (in_array($r, ['DEAN'], true)) return Appointment::ROLE_DEAN;
        if (in_array($r, ['ASSOCIATE_DEAN','ASSOC_DEAN'], true)) return Appointment::ROLE_ASSOC_DEAN;
        if (in_array($r, ['VCAA'], true)) return Appointment::ROLE_VCAA;
        if (in_array($r, ['ASSOCIATE_VCAA','ASSOC_VCAA'], true)) return Appointment::ROLE_ASSOC_VCAA;
        
        // Pattern matching
        if (str_contains($r, 'FACULTY')) return Appointment::ROLE_FACULTY;
        if (str_contains($r, 'DEPT_HEAD') || str_contains($r, 'DEPT')) return Appointment::ROLE_DEPT_HEAD;
        if (str_contains($r, 'PROG')) return Appointment::ROLE_PROG;
        if (str_contains($r, 'DEAN')) return str_contains($r, 'ASSOC') ? Appointment::ROLE_ASSOC_DEAN : Appointment::ROLE_DEAN;
        if (str_contains($r, 'VCAA')) return str_contains($r, 'ASSOC') ? Appointment::ROLE_ASSOC_VCAA : Appointment::ROLE_VCAA;
        
        return null;
    }

    /** Format active appointments for one admin into a lightweight JSON array. */
    protected function buildAppointmentsPayload(User $admin): array
    {
        $admin->loadMissing('appointments');
        $active = $admin->appointments()->active()->get();

        $deptNames = Department::pluck('name', 'id');                                 // id => name
        $progNames = Program::pluck('name', 'id');                                     // id => name
        $deptProgramCounts = Department::withCount('programs')->pluck('programs_count', 'id'); // id => programs_count

        return $active->map(function (Appointment $a) use ($deptNames, $progNames, $deptProgramCounts) {
            $isDept = $a->role === Appointment::ROLE_DEPT;
            $isDeptHead = $a->role === Appointment::ROLE_DEPT_HEAD;
            $isProg = $a->role === Appointment::ROLE_PROG;
            $isDean = $a->role === Appointment::ROLE_DEAN;

            $scopeLabel = 'Institution-wide';
            $roleLabel = $a->role ?? 'Appointment';
            
            if ($isDept || $isDeptHead || $isProg || $isDean) {
                $scopeLabel = (string) ($deptNames[$a->scope_id] ?? 'Unknown Department');
                
                // Dynamic role label based on actual stored role
                if ($isDeptHead) {
                    // Dynamic mapping: Department Head displays as Department Chair (>=2 programs) or Program Chair (<=1 program)
                    $programCount = (int) ($deptProgramCounts[$a->scope_id] ?? 0);
                    if ($programCount >= 2) {
                        $roleLabel = 'Department Chair';
                        $isDept = true;    // treat as department chair
                        $isProg = false;
                    } else {
                        $roleLabel = 'Program Chair';
                        $isDept = false;
                        $isProg = true;    // treat as program chair
                    }
                } elseif ($isDept) {
                    $roleLabel = 'Department Chair';
                } elseif ($isProg) {
                    $roleLabel = 'Program Chair';
                } elseif ($isDean) {
                    $roleLabel = 'Dean';
                }
            } elseif ($a->role === Appointment::ROLE_VCAA) {
                $roleLabel = 'VCAA';
                $scopeLabel = 'Institution-wide';
            } elseif ($a->role === Appointment::ROLE_ASSOC_VCAA) {
                $roleLabel = 'Associate VCAA';
                $scopeLabel = 'Institution-wide';
            } elseif ($a->role === Appointment::ROLE_FACULTY) {
                $roleLabel = 'Faculty';
                $scopeLabel = (string) ($deptNames[$a->scope_id] ?? 'Unknown Department');
            } elseif ($a->role === Appointment::ROLE_ASSOC_DEAN) {
                $roleLabel = 'Associate Dean';
                $scopeLabel = (string) ($deptNames[$a->scope_id] ?? 'Unknown Department');
            } elseif ($a->scope_id && $a->scope_type === 'department') {
                $scopeLabel = (string) ($deptNames[$a->scope_id] ?? 'Unknown Department');
            } elseif ($a->scope_id && $a->scope_type === 'program') {
                $scopeLabel = (string) ($progNames[$a->scope_id] ?? 'Unknown Program');
            } elseif ($a->scope_type) {
                $scopeLabel = ($a->scope_type . ' #' . $a->scope_id);
            }

            return [
                'id'          => (int) $a->id,
                'role'        => $a->role,
                'role_label'  => $roleLabel,
                // is_dept / is_prog reflect dynamic DEPT_HEAD mapping
                'is_dept'     => $isDept,
                'is_dept_head'=> $isDeptHead,
                'is_prog'     => $isProg,
                'is_dean'     => $isDean,
                'scope_id'    => ($a->scope_id && $a->scope_id > 0) ? (int) $a->scope_id : null,
                'scope_label' => $scopeLabel,
                'dept_id'     => ($isDept || $isDeptHead || $isProg || $isDean) ? (int) $a->scope_id : null,
                'program_id'  => null, // Programs are no longer directly assigned
            ];
        })->values()->all();
    }

    /** Unified responder. */
    protected function respond(Request $request, bool $ok, string $message, int $status = 200, array $errors = null, array $extra = []): JsonResponse|RedirectResponse
    {
        if ($this->wantsJson($request)) {
            $payload = array_merge(['ok' => $ok, 'message' => $message], $extra);
            if ($errors !== null) $payload['errors'] = $errors;
            return response()->json($payload, $status);
        }

        if ($status === 422 && $errors) {
            return back()->withErrors($errors)->withInput();
        }

        return $ok ? back()->with('success', $message) : back()->with('error', $message);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'role'          => ['required'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);
        if ($v->fails()) {
            return $this->respond($request, false, 'Validation failed.', 422, $v->errors()->toArray());
        }

        $data = $v->validated();
        $inputRole = $this->normalizeRole($data['role']);
        if (!$inputRole) {
            return $this->respond($request, false, 'Invalid role.', 422, ['role' => ['Invalid role value.']]);
        }

        // Determine actual role based on department program count for chair positions
        $scopeId = $data['department_id'] ?? null;
        $role = $inputRole;
        
        if (in_array($inputRole, [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD]) && $scopeId) {
            // Check department program count to determine chair type
            $department = Department::with('programs')->find($scopeId);
            if ($department) {
                $programCount = $department->programs->count();
                // If user selected legacy DEPT (chair) convert based on program count.
                // If user selected DEPT_HEAD, always keep DEPT_HEAD regardless of program count.
                if ($inputRole === Appointment::ROLE_DEPT && $programCount <= 1) {
                    $role = Appointment::ROLE_PROG; // legacy single-program chair becomes program chair
                } elseif ($inputRole === Appointment::ROLE_DEPT && $programCount > 1) {
                    $role = Appointment::ROLE_DEPT; // multi-program retains legacy dept chair
                } elseif ($inputRole === Appointment::ROLE_DEPT_HEAD) {
                    $role = Appointment::ROLE_DEPT_HEAD; // always store as dept head
                }
            }
        }

        // Only VCAA and Associate VCAA are institution-wide. All other roles require a department.
        if (!in_array($role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA]) && !$scopeId) {
            return $this->respond($request, false, 'Department is required for this role.', 422, ['department_id' => ['Department is required for this role.']]);
        }

        // Check for mutually exclusive roles for the same user
        $userId = (int) $data['user_id'];
        
        // VCAA and Associate VCAA are mutually exclusive
        if ($role === Appointment::ROLE_VCAA || $role === Appointment::ROLE_ASSOC_VCAA) {
            $conflictingRole = $role === Appointment::ROLE_VCAA ? Appointment::ROLE_ASSOC_VCAA : Appointment::ROLE_VCAA;
            $hasConflictingRole = Appointment::active()
                ->where('user_id', $userId)
                ->where('role', $conflictingRole)
                ->exists();
                
            if ($hasConflictingRole) {
                $conflictRoleName = $conflictingRole === Appointment::ROLE_VCAA ? 'VCAA' : 'Associate VCAA';
                return $this->respond($request, false, "User already has {$conflictRoleName} role. VCAA and Associate VCAA roles are mutually exclusive.", 422, [
                    'role' => ["Cannot assign this role. User already has {$conflictRoleName} role."]
                ]);
            }
        }
        
        // Dean and Associate Dean are mutually exclusive
        if ($role === Appointment::ROLE_DEAN || $role === Appointment::ROLE_ASSOC_DEAN) {
            $conflictingRole = $role === Appointment::ROLE_DEAN ? Appointment::ROLE_ASSOC_DEAN : Appointment::ROLE_DEAN;
            $hasConflictingRole = Appointment::active()
                ->where('user_id', $userId)
                ->where('role', $conflictingRole)
                ->exists();
                
            if ($hasConflictingRole) {
                $conflictRoleName = $conflictingRole === Appointment::ROLE_DEAN ? 'Dean' : 'Associate Dean';
                return $this->respond($request, false, "User already has {$conflictRoleName} role. Dean and Associate Dean roles are mutually exclusive.", 422, [
                    'role' => ["Cannot assign this role. User already has {$conflictRoleName} role."]
                ]);
            }
        }

        // Conflict detection rules:
        // - Only one chair (dept or prog) per department
        // - Multiple deans can exist in same department  
        // - Multiple VCAA and Associate VCAA can exist (institution-wide)
        $exists = Appointment::active()
            ->where(function($query) use ($role, $scopeId) {
                if (in_array($role, [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_PROG])) {
                    // For chair positions, check for any existing chair (dept or prog) in the same department
                    // Both chair types use SCOPE_DEPT
                      $query->whereIn('role', [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_PROG])
                          ->where('scope_type', Appointment::SCOPE_DEPT)
                          ->where('scope_id', (int) ($scopeId ?? 0));
                } elseif (in_array($role, [Appointment::ROLE_DEAN, Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA])) {
                    // Dean, VCAA, and Associate VCAA can have multiple appointments - no conflict check needed
                    $query->whereRaw('1 = 0'); // Always false - no conflicts for these roles
                } else {
                    // For other roles, check exact role match
                    $query->where('role', $role)
                          ->where('scope_id', (int) ($scopeId ?? 0));
                }
            })
            ->exists();

        if ($exists) {
            $field = 'department_id';
            return $this->respond($request, false, 'An active chair already exists for this selection.', 422, [
                $field => ['An active chair already exists for this selection.'],
            ]);
        }

        $appt = new Appointment([
            'user_id' => (int) $data['user_id'],
            'role'    => $role,
            'status'  => 'active',
        ]);
        
        // Set scope based on role type
        if (in_array($role, [Appointment::ROLE_FACULTY, Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_PROG, Appointment::ROLE_DEAN, Appointment::ROLE_ASSOC_DEAN])) {
            // Faculty, Chair types, Dean, and Associate Dean use department scope
            $appt->role = $role;
            $appt->scope_type = Appointment::SCOPE_DEPT;
            $appt->scope_id = (int) ($data['department_id'] ?? 0);
        } elseif (in_array($role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA])) {
            // Institution-wide roles (VCAA, Associate VCAA)
            $appt->role = $role;
            $appt->scope_type = Appointment::SCOPE_INSTITUTION;
            $appt->scope_id = 0; // Use 0 for institution-wide roles since column doesn't allow null
        } else {
            // Other roles use default setRoleAndScope logic
            $appt->setRoleAndScope($role, null, null);
        }
        $appt->save();

        // If adding a leadership role, replace any existing Faculty appointment(s)
        if (in_array($role, [Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_DEAN, Appointment::ROLE_ASSOC_DEAN])) {
            Appointment::where('user_id', (int) $data['user_id'])
                ->where('role', Appointment::ROLE_FACULTY)
                ->delete();
        }

        $admin = User::findOrFail((int) $data['user_id']);
        $appointments = $this->buildAppointmentsPayload($admin);

        return $this->respond($request, true, 'Appointment created.', 200, null, [
            'admin_id'     => $admin->id,
            'appointments' => $appointments,
        ]);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse|RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'role'          => ['required', 'string'],
            'department_id' => ['nullable', 'integer', 'min:1', 'exists:departments,id'],
        ]);
        if ($v->fails()) {
            return $this->respond($request, false, 'Validation failed.', 422, $v->errors()->toArray());
        }

        $data = $v->validated();
        $inputRole = $this->normalizeRole($data['role']);
        if (!$inputRole) {
            return $this->respond($request, false, 'Invalid role.', 422, ['role' => ['Invalid role value.']]);
        }

        $scopeId = $data['department_id'] ?? null;
        $role = $inputRole;
        
        // For Program/Department Chair selection, determine actual role based on department's program count
        if (in_array($inputRole, [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD]) && $scopeId) {
            $department = Department::with('programs')->find($scopeId);
            if (!$department) {
                return $this->respond($request, false, 'Selected department not found.', 422, [
                    'department_id' => ['Selected department not found.']
                ]);
            }
            
            $programCount = $department->programs->count();
            if ($inputRole === Appointment::ROLE_DEPT) {
                // Legacy dept chair logic: convert to program chair if single program
                if ($programCount <= 1) {
                    $role = Appointment::ROLE_PROG;
                } else {
                    $role = Appointment::ROLE_DEPT;
                }
            } elseif ($inputRole === Appointment::ROLE_DEPT_HEAD) {
                // Always store as dept head
                $role = Appointment::ROLE_DEPT_HEAD;
            }
        }

        // Validate department requirement based on final determined role
        if (in_array($role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA])) {
            // Institution-wide roles should not have department_id - clear it if provided
            $scopeId = null; // Force null for institution-wide roles
        } elseif (!$scopeId) {
            // All other roles require a department
            return $this->respond($request, false, 'Department is required for this role.', 422, [
                'department_id' => ['Department is required for this role.']
            ]);
        }

        // Conflict detection rules:
        // - Only one chair (dept or prog) per department
        // - Multiple deans can exist in same department  
        // - Multiple VCAA and Associate VCAA can exist (institution-wide)
        $exists = Appointment::active()
            ->where(function($query) use ($role, $scopeId) {
                if (in_array($role, [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_PROG])) {
                    // For chair positions, check for any existing chair (dept or prog) in the same department
                    // Both chair types use SCOPE_DEPT
                      $query->whereIn('role', [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_PROG])
                          ->where('scope_type', Appointment::SCOPE_DEPT)
                          ->where('scope_id', (int) ($scopeId ?? 0));
                } elseif (in_array($role, [Appointment::ROLE_DEAN, Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA])) {
                    // Dean, VCAA, and Associate VCAA can have multiple appointments - no conflict check needed
                    $query->whereRaw('1 = 0'); // Always false - no conflicts for these roles
                } else {
                    // For other roles, check exact role match
                    $query->where('role', $role)
                          ->where('scope_id', (int) ($scopeId ?? 0));
                }
            })
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($exists) {
            $field = 'department_id';
            $conflictMessage = in_array($role, [Appointment::ROLE_DEPT, Appointment::ROLE_PROG]) 
                ? 'An active chair already exists for this department.' 
                : 'A conflict exists with an existing appointment.';
            return $this->respond($request, false, $conflictMessage, 422, [
                $field => [$conflictMessage],
            ]);
        }

        // Store original values for comparison
        $originalRole = $appointment->role;
        $originalScopeType = $appointment->scope_type;
        $originalScopeId = $appointment->scope_id;

        // Restrict converting Department Head into Dean / Associate Dean when multiple leadership roles are active
        $leadershipRolesForRestriction = [Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_DEAN, Appointment::ROLE_ASSOC_DEAN];
        if ($originalRole === Appointment::ROLE_DEPT_HEAD
            && in_array($role, [Appointment::ROLE_DEAN, Appointment::ROLE_ASSOC_DEAN], true)) {
            $otherLeadershipCount = Appointment::active()
                ->where('user_id', $appointment->user_id)
                ->whereIn('role', $leadershipRolesForRestriction)
                ->where('id', '!=', $appointment->id)
                ->count();
            if ($otherLeadershipCount > 0) {
                return $this->respond($request, false, 'Cannot convert Department Head while another leadership role is active.', 422, [
                    'role' => ['Department Head cannot be converted to Dean or Associate Dean when another leadership role exists.']
                ]);
            }
        }

        // Restrict converting Dean/Associate Dean into Department Head when multiple leadership roles are active (mirror rule)
        if (in_array($originalRole, [Appointment::ROLE_DEAN, Appointment::ROLE_ASSOC_DEAN], true)
            && $role === Appointment::ROLE_DEPT_HEAD) {
            $otherLeadershipCount = Appointment::active()
                ->where('user_id', $appointment->user_id)
                ->whereIn('role', $leadershipRolesForRestriction)
                ->where('id', '!=', $appointment->id)
                ->count();
            if ($otherLeadershipCount > 0) {
                return $this->respond($request, false, 'Cannot convert Dean/Associate Dean to Department Head while another leadership role is active.', 422, [
                    'role' => ['Dean or Associate Dean cannot be converted to Department Head when another leadership role exists.']
                ]);
            }
        }

        // Update appointment with new role and scope information
        if (in_array($role, [Appointment::ROLE_FACULTY, Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_PROG, Appointment::ROLE_DEAN, Appointment::ROLE_ASSOC_DEAN])) {
            // Faculty, Chair types, Dean, and Associate Dean use department scope
            $appointment->role = $role;
            $appointment->scope_type = Appointment::SCOPE_DEPT;
            $appointment->scope_id = (int) $scopeId;
        } elseif (in_array($role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA])) {
            // Institution-wide roles (VCAA, Associate VCAA)
            $appointment->role = $role;
            $appointment->scope_type = Appointment::SCOPE_INSTITUTION;
            $appointment->scope_id = 0; // Use 0 for institution-wide roles since column doesn't allow null
        } else {
            // Fallback for any other roles
            $appointment->role = $role;
            $appointment->scope_type = Appointment::SCOPE_INSTITUTION;
            $appointment->scope_id = 0;
        }
        
        // Force update all fields even if some haven't changed
        $appointment->timestamps = true;
        $appointment->touch(); // Update the updated_at timestamp
        
        // Save the updated appointment
        $saved = $appointment->save();
        
        if (!$saved) {
            return $this->respond($request, false, 'Failed to update appointment.', 500);
        }

        // If updated appointment is a leadership role, propagate department scope to other active leadership roles
        $leadershipRoles = [
            Appointment::ROLE_DEPT_HEAD,
            Appointment::ROLE_DEAN,
            Appointment::ROLE_ASSOC_DEAN,
        ];
        if (in_array($appointment->role, $leadershipRoles, true)
            && $appointment->scope_type === Appointment::SCOPE_DEPT
            && $appointment->scope_id) {
            Appointment::where('user_id', $appointment->user_id)
                ->where('status', 'active')
                ->whereIn('role', $leadershipRoles)
                ->where('id', '!=', $appointment->id)
                ->update([
                    'scope_type' => Appointment::SCOPE_DEPT,
                    'scope_id'   => (int) $appointment->scope_id,
                    'updated_at' => now(),
                ]);
        }

        $admin = $appointment->user()->firstOrFail();
        $appointments = $this->buildAppointmentsPayload($admin);

        return $this->respond($request, true, 'Appointment updated.', 200, null, [
            'admin_id'     => $admin->id,
            'appointments' => $appointments,
        ]);
    }

    public function end(Request $request, Appointment $appointment): JsonResponse|RedirectResponse
    {
        // Store admin reference and last-known department before deleting appointment
        $admin = $appointment->user()->firstOrFail();
        $deletedDeptId = null;
        if ($appointment->scope_type === Appointment::SCOPE_DEPT && $appointment->scope_id) {
            $deletedDeptId = (int) $appointment->scope_id;
        }

        // Hard delete the appointment from database
        $appointment->delete();

        // If user has no more active appointments, restore Faculty with the same department (when available)
        $hasAnyActive = Appointment::active()->where('user_id', $admin->id)->exists();
        if (!$hasAnyActive && $deletedDeptId) {
            $fallback = new Appointment([
                'user_id' => $admin->id,
                'role'    => Appointment::ROLE_FACULTY,
                'status'  => 'active',
            ]);
            // Ensure scope set correctly for Faculty role with department context
            $fallback->setRoleAndScope(Appointment::ROLE_FACULTY, $deletedDeptId, null);
            $fallback->save();
        }

        $appointments = $this->buildAppointmentsPayload($admin);

        return $this->respond($request, true, 'Appointment deleted.', 200, null, [
            'admin_id'     => $admin->id,
            'appointments' => $appointments,
        ]);
    }

    public function destroy(Request $request, Appointment $appointment): JsonResponse|RedirectResponse
    {
        $admin = $appointment->user()->firstOrFail();
        $appointment->delete();

        $appointments = $this->buildAppointmentsPayload($admin);

        return $this->respond($request, true, 'Appointment removed.', 200, null, [
            'admin_id'     => $admin->id,
            'appointments' => $appointments,
        ]);
    }
}
