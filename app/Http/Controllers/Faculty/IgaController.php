<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/IgaController.php
// * Description: Faculty IGA master data controller mirroring SO/SDG behavior
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Iga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class IgaController extends Controller
{
    public function filter(Request $request)
    {
        try {
            $department = $request->query('department', 'all');

            $user = Auth::guard('faculty')->user();
            $appointments = $user && method_exists($user, 'appointments')
                ? $user->appointments()->active()->get(['role', 'scope_type', 'scope_id'])
                : collect();

            // Institution-wide only for VCAA/ASSOC_VCAA. Deans should be department-scoped unless explicitly handled elsewhere.
            $hasInstitutionWide = $appointments->contains(function ($appointment) {
                return in_array($appointment->role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA]);
            });

            $query = Iga::with(['department:id,code,name'])->ordered();

            if ($hasInstitutionWide) {
                // Institution-wide: honor explicit department filter if provided
                if ($department !== 'all') {
                    $deptId = filter_var($department, FILTER_VALIDATE_INT);
                    if ($deptId) {
                        $query->where('department_id', $deptId);
                    }
                }
            } else {
                // Department-scoped users: restrict strictly to their departments (no institution-wide/nulls)
                $deptIds = $appointments
                    ->filter(function ($appt) {
                        return in_array($appt->scope_type, [Appointment::SCOPE_DEPT, Appointment::SCOPE_FACULTY]) && !empty($appt->scope_id);
                    })
                    ->pluck('scope_id')
                    ->unique()
                    ->values()
                    ->all();

                $deptIds = array_map('intval', $deptIds);

                if ($department !== 'all') {
                    $deptId = filter_var($department, FILTER_VALIDATE_INT);
                    if ($deptId && in_array($deptId, $deptIds, true)) {
                        $query->where('department_id', $deptId);
                    } else {
                        // If invalid/unauthorized department requested, fall back to allowed departments only
                        if (!empty($deptIds)) {
                            $query->whereIn('department_id', $deptIds);
                        } else {
                            // No department scope — return empty result
                            $query->whereRaw('0=1');
                        }
                    }
                } else {
                    // 'all' for scoped users: only their departments
                    if (!empty($deptIds)) {
                        $query->whereIn('department_id', $deptIds);
                    } else {
                        $query->whereRaw('0=1');
                    }
                }
            }

            $igas = $query->get();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'igas' => $igas,
                    'count' => $igas->count(),
                    'department' => $department,
                ]);
            }
            return redirect()->route('faculty.dashboard');
        } catch (\Throwable $e) {
            Log::error('IGA Filter Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load IGAs'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::guard('faculty')->user();
            $appointments = $user && method_exists($user, 'appointments')
                ? $user->appointments()->active()->get(['role', 'scope_type', 'scope_id'])
                : collect();
            $hasInstitutionWide = $appointments->contains(function ($appointment) {
                return in_array($appointment->role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA]);
            });

            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ];
            if ($hasInstitutionWide) {
                $rules['department_id'] = 'required|integer|exists:departments,id';
            }
            $validated = $request->validate($rules);

            // Resolve department id based on scope
            if ($hasInstitutionWide) {
                $departmentId = (int) $validated['department_id'];
            } else {
                // Derive user's department from appointments
                $deptIds = $appointments
                    ->filter(function ($appt) {
                        return in_array($appt->scope_type, [Appointment::SCOPE_DEPT, Appointment::SCOPE_FACULTY]) && !empty($appt->scope_id);
                    })
                    ->pluck('scope_id')->map(fn($id) => (int) $id)->unique()->values();
                $departmentId = $deptIds->first();
                if (!$departmentId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Department is required to create an IGA.'
                    ], 422);
                }
            }

            $iga = Iga::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'department_id' => $departmentId,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'IGA created successfully!',
                    'iga' => $iga,
                ], 201);
            }
            return redirect()->route('faculty.master-data.index')->with('success', 'IGA created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('IGA Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create IGA'], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $user = Auth::guard('faculty')->user();
            $appointments = $user && method_exists($user, 'appointments')
                ? $user->appointments()->active()->get(['role', 'scope_type', 'scope_id'])
                : collect();
            $hasInstitutionWide = $appointments->contains(function ($appointment) {
                return in_array($appointment->role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA]);
            });

            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ];
            if ($hasInstitutionWide) {
                $rules['department_id'] = 'nullable|integer|exists:departments,id';
            }
            $validated = $request->validate($rules);

            $iga = Iga::findOrFail($id);

            // Scope enforcement for non-institution-wide users
            if (!$hasInstitutionWide) {
                $deptIds = $appointments
                    ->filter(function ($appt) {
                        return in_array($appt->scope_type, [Appointment::SCOPE_DEPT, Appointment::SCOPE_FACULTY]) && !empty($appt->scope_id);
                    })
                    ->pluck('scope_id')->map(fn($x) => (int) $x)->unique()->values()->all();
                if (empty($deptIds) || !in_array((int) $iga->department_id, $deptIds, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to update this IGA.'
                    ], 403);
                }
            }

            $payload = [
                'title' => $validated['title'],
                'description' => $validated['description'],
            ];
            if ($hasInstitutionWide && !empty($validated['department_id'])) {
                $payload['department_id'] = (int) $validated['department_id'];
            }
            $iga->update($payload);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'IGA updated successfully!',
                    'iga' => $iga,
                ]);
            }
            return redirect()->route('faculty.master-data.index')->with('success', 'IGA updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('IGA Update Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update IGA'], 500);
        }
    }

    public function destroy(Request $request, int $id)
    {
        try {
            $user = Auth::guard('faculty')->user();
            $appointments = $user && method_exists($user, 'appointments')
                ? $user->appointments()->active()->get(['role', 'scope_type', 'scope_id'])
                : collect();
            $hasInstitutionWide = $appointments->contains(function ($appointment) {
                return in_array($appointment->role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA]);
            });

            $iga = Iga::findOrFail($id);
            if (!$hasInstitutionWide) {
                $deptIds = $appointments
                    ->filter(function ($appt) {
                        return in_array($appt->scope_type, [Appointment::SCOPE_DEPT, Appointment::SCOPE_FACULTY]) && !empty($appt->scope_id);
                    })
                    ->pluck('scope_id')->map(fn($x) => (int) $x)->unique()->values()->all();
                if (empty($deptIds) || !in_array((int) $iga->department_id, $deptIds, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to delete this IGA.'
                    ], 403);
                }
            }
            $iga->delete();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'IGA deleted successfully!',
                    'id' => $id,
                ]);
            }
            return redirect()->route('faculty.master-data.index')->with('success', 'IGA deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('IGA Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete IGA'], 500);
        }
    }
}
