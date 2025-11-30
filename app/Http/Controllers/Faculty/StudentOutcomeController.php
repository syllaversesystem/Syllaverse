<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/StudentOutcomeController.php
// * Description: Simple Student Outcomes controller for Faculty module
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\StudentOutcome;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentOutcomeController extends Controller
{
    /**
     * Store a new Student Outcome
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::guard('faculty')->user();
            $appointments = method_exists($user, 'appointments') ? $user->appointments()->active()->get() : collect();
            $hasInstitutionWide = $appointments->contains(function ($appointment) {
                return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
            });

            // Validation differs by scope
            $rules = [
                'title' => 'nullable|string|max:255',
                'description' => 'required|string|max:2000',
            ];
            if ($hasInstitutionWide) {
                $rules['department_id'] = 'required|integer|exists:departments,id';
            }
            $validated = $request->validate($rules);

            // Resolve department id
            if ($hasInstitutionWide) {
                $departmentId = (int) $validated['department_id'];
            } else {
                // Use robust helper to find the user's primary department
                $departmentId = method_exists($user, 'getPrimaryDepartmentId')
                    ? $user->getPrimaryDepartmentId()
                    : null;
                // Fallback: derive from any active appointment with a department-like scope
                if (!$departmentId && $appointments->isNotEmpty()) {
                    $firstDeptAppt = $appointments->first(function ($appt) {
                        return in_array($appt->scope_type, [\App\Models\Appointment::SCOPE_DEPT, \App\Models\Appointment::SCOPE_FACULTY]) && !empty($appt->scope_id);
                    });
                    if ($firstDeptAppt) {
                        $departmentId = (int) $firstDeptAppt->scope_id;
                    }
                }
            }

            if (!$departmentId) {
                // No department resolved; block creation
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Department is required to create a Student Outcome.'
                    ], 422);
                }
                return back()->withErrors(['department_id' => 'Department is required to create a Student Outcome.'])->withInput();
            }

            // Create the Student Outcome
            $so = StudentOutcome::create([
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'],
                'department_id' => $departmentId,
            ]);

            // Load the department relationship
            $so->load('department');

            // Return appropriate response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student Outcome created successfully!',
                    'so' => $so
                ], 201);
            }

            return redirect()->route('faculty.dashboard')
                ->with('success', 'Student Outcome created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('SO Creation Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the Student Outcome.'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'An error occurred while creating the Student Outcome.'])->withInput();
        }
    }

    /**
     * Update an existing Student Outcome
     */
    public function update(Request $request, int $id)
    {
        try {
            $user = Auth::guard('faculty')->user();
            $appointments = method_exists($user, 'appointments') ? $user->appointments()->active()->get() : collect();
            $hasInstitutionWide = $appointments->contains(function ($appointment) {
                return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
            });

            $rules = [
                'title' => 'nullable|string|max:255',
                'description' => 'required|string|max:2000',
            ];
            if ($hasInstitutionWide) {
                $rules['department_id'] = 'nullable|integer|exists:departments,id';
            }
            $validated = $request->validate($rules);

            $so = StudentOutcome::findOrFail($id);

            $payload = [
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'],
            ];

            if ($hasInstitutionWide && !empty($validated['department_id'])) {
                $payload['department_id'] = $validated['department_id'];
            }

            $so->update($payload);

            $so->load('department');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student Outcome updated successfully!',
                    'so' => $so
                ]);
            }

            return redirect()->route('faculty.dashboard')
                ->with('success', 'Student Outcome updated successfully!');

        } catch (\Exception $e) {
            Log::error('SO Update Error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the Student Outcome.'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'An error occurred while updating the Student Outcome.']);
        }
    }

    /**
     * Delete a Student Outcome
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $so = StudentOutcome::findOrFail($id);
            $so->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student Outcome deleted successfully!',
                    'id' => $id,
                ]);
            }

            return redirect()->route('faculty.dashboard')
                ->with('success', 'Student Outcome deleted successfully!');

        } catch (\Exception $e) {
            Log::error('SO Delete Error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the Student Outcome.'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'An error occurred while deleting the Student Outcome.']);
        }
    }

    /**
     * Filter Student Outcomes by department via AJAX
     */
    public function filterByDepartment(Request $request)
    {
        try {
            $user = Auth::guard('faculty')->user() ?? Auth::user();
            $appointments = $user?->appointments()->active()->get() ?? collect();

            // Determine if user has institution-wide only roles (VCAA / ASSOC_VCAA)
            $hasInstitutionWideOnly = $appointments->isNotEmpty() && $appointments->every(function($a){
                return in_array($a->role, ['VCAA','ASSOC_VCAA']);
            });

            // Resolve a single department id from any department-scoped appointment
            $deptScopedRoles = [
                \App\Models\Appointment::ROLE_DEPT,
                \App\Models\Appointment::ROLE_DEPT_HEAD,
                \App\Models\Appointment::ROLE_PROG,
                \App\Models\Appointment::ROLE_DEAN,
                \App\Models\Appointment::ROLE_ASSOC_DEAN,
                \App\Models\Appointment::ROLE_FACULTY,
            ];
            $deptAppt = $appointments->first(function($a) use ($deptScopedRoles){
                return in_array($a->role, $deptScopedRoles, true) && $a->scope_type === \App\Models\Appointment::SCOPE_DEPT && !empty($a->scope_id);
            });
            $departmentId = $deptAppt?->scope_id;

            // Query student outcomes limited to department unless institution-wide only
            $soQuery = StudentOutcome::with('department');
            if ($departmentId && !$hasInstitutionWideOnly) {
                $soQuery->where('department_id', (int) $departmentId);
            }
            $studentOutcomes = $soQuery->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'studentOutcomes' => $studentOutcomes,
                    'count' => $studentOutcomes->count(),
                    'department_id' => $departmentId ? (int)$departmentId : null,
                    'scoped' => (bool)($departmentId && !$hasInstitutionWideOnly),
                ]);
            }

            // Non-AJAX fallback – redirect to dashboard with info
            return redirect()->route('faculty.dashboard')->with([
                'so_department_id' => $departmentId,
                'so_scoped' => (bool)($departmentId && !$hasInstitutionWideOnly),
            ]);
                
        } catch (\Exception $e) {
            Log::error('SO Filter Error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while filtering Student Outcomes.'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'An error occurred while filtering Student Outcomes.']);
        }
    }
}