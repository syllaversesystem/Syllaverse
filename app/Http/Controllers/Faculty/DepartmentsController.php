<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DepartmentsController extends Controller
{
    /**
     * Check if the current user has institution-wide role (VCAA or Associate VCAA).
     */
    private function hasInstitutionWideRole(): bool
    {
        return Auth::guard('faculty')->user()->appointments()
            ->active()
            ->whereIn('role', [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA])
            ->exists();
    }

    /**
     * Display a listing of departments.
     */
    public function index(): View
    {
        // Check if user has institution-wide role
        if (!$this->hasInstitutionWideRole()) {
            abort(403, 'Access denied. Only users with institution-wide roles can manage departments.');
        }

        try {
            $departments = Department::orderBy('name')->get();
            
            return view('faculty.departments.index', compact('departments'));
        } catch (\Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());
            return view('faculty.departments.index', ['departments' => collect()]);
        }
    }

    /**
     * Get departments table content for AJAX requests.
     */
    public function tableContent(Request $request): JsonResponse
    {
        // Check if user has institution-wide role
        if (!$this->hasInstitutionWideRole()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only users with institution-wide roles can manage departments.'
            ], 403);
        }

        try {
            $q = trim((string) $request->query('q', ''));

            $query = Department::query();
            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            }

            $departments = $query->orderBy('name')->get();
            $isSearch = $q !== '';
            
            $html = view('faculty.departments.partials.table-content', compact('departments', 'isSearch'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $departments->count(),
                'search' => $q,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching departments table content: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load departments table.'
            ], 500);
        }
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user has institution-wide role
        if (!$this->hasInstitutionWideRole()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only users with institution-wide roles can manage departments.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:departments,name',
                'code' => 'required|string|max:50|unique:departments,code',
            ]);

            $department = Department::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully!',
                'department' => $department
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating department: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the department.'
            ], 500);
        }
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        // Check if user has institution-wide role
        if (!$this->hasInstitutionWideRole()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only users with institution-wide roles can manage departments.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
                'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
            ]);

            $department->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully!',
                'department' => $department->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating department: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the department.'
            ], 500);
        }
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department): JsonResponse
    {
        // Check if user has institution-wide role
        if (!$this->hasInstitutionWideRole()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only users with institution-wide roles can manage departments.'
            ], 403);
        }

        try {
            // Check if department has associated programs or courses
            $hasPrograms = $department->programs()->exists();
            $hasCourses = $department->courses()->exists();
            
            if ($hasPrograms || $hasCourses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete department. It has associated programs or courses.'
                ], 422);
            }

            $departmentName = $department->name;
            $department->delete();

            return response()->json([
                'success' => true,
                'message' => "Department '{$departmentName}' deleted successfully!"
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting department: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the department.'
            ], 500);
        }
    }
}