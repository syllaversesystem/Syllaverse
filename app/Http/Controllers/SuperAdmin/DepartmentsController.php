<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class DepartmentsController extends Controller
{
    /** Display a listing of departments (no VCAA guard; superadmin module). */
    public function index(): View
    {
        try {
            $departments = Department::orderBy('name')->get();
            return view('superadmin.departments.index', compact('departments'));
        } catch (\Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());
            return view('superadmin.departments.index', ['departments' => collect()]);
        }
    }

    /** Get departments table content for AJAX requests. */
    public function tableContent(Request $request): JsonResponse
    {
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

            $html = view('superadmin.departments.partials.table-content', compact('departments', 'isSearch'))->render();

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

    /** Store a newly created department. */
    public function store(Request $request): JsonResponse
    {
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

    /** Update an existing department. */
    public function update(Request $request, Department $department): JsonResponse
    {
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

    /** Remove the specified department. */
    public function destroy(Department $department): JsonResponse
    {
        try {
            $hasPrograms = $department->programs()->exists();
            $hasCourses = method_exists($department, 'courses') ? $department->courses()->exists() : false;

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
