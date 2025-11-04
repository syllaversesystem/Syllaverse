<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/SdgController.php
// * Description: Faculty SDG master data controller mirroring SO behavior
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Sdg;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SdgController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = Auth::guard('faculty')->user();
            $appointments = method_exists($user, 'appointments') ? $user->appointments()->active()->get() : collect();
            $hasInstitutionWide = $appointments->contains(function ($appointment) {
                return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
            });

            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ];
            if ($hasInstitutionWide) {
                $rules['department_id'] = 'required|integer|exists:departments,id';
            }
            $validated = $request->validate($rules);

            if ($hasInstitutionWide) {
                $departmentId = (int) $validated['department_id'];
            } else {
                $departmentId = method_exists($user, 'getPrimaryDepartmentId')
                    ? $user->getPrimaryDepartmentId()
                    : null;
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
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Department is required to create an SDG.'
                    ], 422);
                }
                return back()->withErrors(['department_id' => 'Department is required to create an SDG.'])->withInput();
            }

            $sdg = Sdg::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'department_id' => $departmentId,
            ]);

            $sdg->load('department');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SDG created successfully!',
                    'sdg' => $sdg
                ], 201);
            }

            return redirect()->route('faculty.dashboard')
                ->with('success', 'SDG created successfully!');

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
            Log::error('SDG Creation Error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the SDG.'
                ], 500);
            }
            return back()->withErrors(['error' => 'An error occurred while creating the SDG.'])->withInput();
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $user = Auth::guard('faculty')->user();
            $appointments = method_exists($user, 'appointments') ? $user->appointments()->active()->get() : collect();
            $hasInstitutionWide = $appointments->contains(function ($appointment) {
                return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
            });

            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ];
            if ($hasInstitutionWide) {
                $rules['department_id'] = 'nullable|integer|exists:departments,id';
            }
            $validated = $request->validate($rules);

            $sdg = Sdg::findOrFail($id);
            $payload = [
                'title' => $validated['title'],
                'description' => $validated['description'],
            ];
            if ($hasInstitutionWide && !empty($validated['department_id'])) {
                $payload['department_id'] = (int) $validated['department_id'];
            }

            $sdg->update($payload);
            $sdg->load('department');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SDG updated successfully!',
                    'sdg' => $sdg
                ]);
            }

            return redirect()->route('faculty.dashboard')
                ->with('success', 'SDG updated successfully!');

        } catch (\Exception $e) {
            Log::error('SDG Update Error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the SDG.'
                ], 500);
            }
            return back()->withErrors(['error' => 'An error occurred while updating the SDG.']);
        }
    }

    public function destroy(Request $request, int $id)
    {
        try {
            $sdg = Sdg::findOrFail($id);
            $sdg->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SDG deleted successfully!',
                    'id' => $id,
                ]);
            }

            return redirect()->route('faculty.dashboard')
                ->with('success', 'SDG deleted successfully!');

        } catch (\Exception $e) {
            Log::error('SDG Delete Error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the SDG.'
                ], 500);
            }
            return back()->withErrors(['error' => 'An error occurred while deleting the SDG.']);
        }
    }

    public function filterByDepartment(Request $request)
    {
        try {
            $user = Auth::guard('faculty')->user();
            $departmentFilter = $request->get('department');

            $query = Sdg::with('department');
            if ($departmentFilter && $departmentFilter !== 'all') {
                $query->where('department_id', $departmentFilter);
            }
            $sdgs = $query->ordered()->get();

            $userAppointments = method_exists($user, 'appointments') ? $user->appointments()->active()->get() : collect();
            $showDepartmentFilter = $userAppointments->contains(function ($appointment) {
                return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'sdgs' => $sdgs,
                    'count' => $sdgs->count(),
                    'department_filter' => $departmentFilter,
                    'showDepartmentFilter' => $showDepartmentFilter,
                ]);
            }

            return redirect()->route('faculty.dashboard');
        } catch (\Exception $e) {
            Log::error('SDG Filter Error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while loading SDGs.'
                ], 500);
            }
            return back()->withErrors(['error' => 'An error occurred while loading SDGs.']);
        }
    }
}
