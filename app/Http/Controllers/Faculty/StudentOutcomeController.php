<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/StudentOutcomeController.php
// * Description: Dedicated controller for Student Outcomes (SO) – add/update/delete/reorder with JSON support
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-01-20] Copied from admin for Faculty module
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\StudentOutcome;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentOutcomeController extends Controller
{
    /**
     * Small helper: determine if the request expects a JSON response.
     * This keeps our endpoints compatible with both AJAX and classic redirects.
     */
    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }

    /**
     * Helper: simple authorization check for managing SO.
     * This allows admin, faculty, or (if available) Dept/Prog chairs.
     * Updated to allow faculty users to manage master data.
     */
    protected function canManage(): bool
    {
        $u = Auth::user();
        if (!$u) return false;

        // Allow admin and faculty users
        if (in_array($u->role, ['admin', 'faculty'])) return true;

        // These helpers exist in your User model per shared code
        $dept = \method_exists($u, 'isDeptChair') && $u->isDeptChair();
        $prog = \method_exists($u, 'isProgChair') && $u->isProgChair();
        return $dept || $prog;
    }

    // ░░░ START: CRUD ░░░

    /**
     * Store a new Student Outcome.
     * Plain-English: Take the title, description and department_id and save.
     * For faculty role users, automatically use their department.
     */
    public function store(Request $request)
    {
        if (!$this->canManage()) {
            if ($this->wantsJson($request)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Forbidden');
        }

        $user = Auth::user();
        
        // Determine validation rules based on user role
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ];
        
        // Admin and chairs can select department; faculty uses their own department
        if ($user->role !== 'faculty') {
            $rules['department_id'] = 'required|integer|exists:departments,id';
        }

        $validated = $request->validate($rules);

        // Get department_id based on user role
        if ($user->role === 'faculty') {
            $departmentId = $user->getPrimaryDepartmentId();
            if (!$departmentId) {
                if ($this->wantsJson($request)) {
                    return response()->json(['message' => 'No department found for your account. Please contact administration.'], 422);
                }
                return back()->withErrors(['department' => 'No department found for your account. Please contact administration.']);
            }
        } else {
            $departmentId = $validated['department_id'];
        }

        $so = StudentOutcome::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'department_id' => $departmentId,
        ]);

        // Load the department relationship for the response
        $so->load('department');

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => "Student Outcome added successfully!",
                'so'      => $so,
            ], 201);
        }

        return redirect()->route('faculty.master-data.index', [
            'tab'    => 'soilo',
            'subtab' => 'so',
        ])->with('success', "Student Outcome added successfully!");
    }

    /**
     * Update an existing Student Outcome.
     * Plain-English: Edit the SO's title, description and department.
     * For faculty role users, department is fixed to their department.
     */
    public function update(Request $request, int $id)
    {
        if (!$this->canManage()) {
            if ($this->wantsJson($request)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Forbidden');
        }

        $user = Auth::user();
        
        // Determine validation rules based on user role
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ];
        
        // Admin and chairs can change department; faculty keeps their department
        if ($user->role !== 'faculty') {
            $rules['department_id'] = 'required|integer|exists:departments,id';
        }

        $validated = $request->validate($rules);

        $so = StudentOutcome::findOrFail($id);

        // Get department_id based on user role
        if ($user->role === 'faculty') {
            $departmentId = $user->getPrimaryDepartmentId();
            if (!$departmentId) {
                if ($this->wantsJson($request)) {
                    return response()->json(['message' => 'No department found for your account. Please contact administration.'], 422);
                }
                return back()->withErrors(['department' => 'No department found for your account. Please contact administration.']);
            }
        } else {
            $departmentId = $validated['department_id'];
        }

        $so->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'department_id' => $departmentId,
        ]);

        // Load the department relationship for the response
        $so->load('department');

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Student Outcome updated successfully!',
                'so'      => $so,
            ]);
        }

        return redirect()->route('faculty.master-data.index', [
            'tab'    => 'soilo',
            'subtab' => 'so',
        ])->with('success', 'SO updated successfully!');
    }

    /**
     * Destroy a Student Outcome.
     * Plain-English: Remove the SO; front-end will drop the row.
     */
    public function destroy(Request $request, int $id)
    {
        if (!$this->canManage()) {
            if ($this->wantsJson($request)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Forbidden');
        }

        $so = StudentOutcome::findOrFail($id);
        $so->delete();

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Student Outcome deleted successfully!',
                'id'      => $id,
            ]);
        }

        return redirect()->route('faculty.master-data.index', [
            'tab'    => 'soilo',
            'subtab' => 'so',
        ])->with('success', 'Student Outcome deleted successfully!');
    }

    // ░░░ END: CRUD ░░░
}