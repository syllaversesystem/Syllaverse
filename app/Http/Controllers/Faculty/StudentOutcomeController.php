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
            $user = Auth::user();
            $departmentFilter = $request->get('department');
            
            // Build student outcomes query with optional department filter
            $soQuery = StudentOutcome::with(['department']);
            if ($departmentFilter && $departmentFilter !== 'all') {
                $soQuery->where('department_id', $departmentFilter);
            }
            $studentOutcomes = $soQuery->get();
            
            // Get all departments for context
            $departments = Department::orderBy('code')->get();
            
            // Get user permissions (same logic as MasterDataController)
            $userAppointments = $user->appointments()->active()->get();
            
            // Check if user has VCAA/ASSOC_VCAA roles for department filter
            $showDepartmentFilter = $userAppointments->contains(function($appointment) {
                return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
            });
            
            // Return JSON response with data
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'studentOutcomes' => $studentOutcomes,
                    'count' => $studentOutcomes->count(),
                    'department_filter' => $departmentFilter
                ]);
            }
            
            // Fallback to redirect for non-AJAX requests
            return redirect()->route('faculty.dashboard')
                ->with('department', $departmentFilter);
                
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