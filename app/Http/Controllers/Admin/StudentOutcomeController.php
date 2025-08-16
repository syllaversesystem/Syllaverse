<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/StudentOutcomeController.php
// * Description: Dedicated controller for Student Outcomes (SO) – add/update/delete/reorder with JSON support
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-18] Initial creation – extracted SO endpoints from MasterDataController,
//              added consistent JSON responses for AJAX (store/update/destroy/reorder).
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentOutcome;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * This allows admin or (if available) Dept/Prog chairs.
     * Adjust per your policy (e.g., admin-only) if needed.
     */
    protected function canManage(): bool
    {
        $u = Auth::user();
        if (!$u) return false;

        if ($u->role === 'admin') return true;

        // These helpers exist in your User model per shared code
        $dept = \method_exists($u, 'isDeptChair') && $u->isDeptChair();
        $prog = \method_exists($u, 'isProgChair') && $u->isProgChair();
        return $dept || $prog;
    }

    // ░░░ START: CRUD ░░░

    /**
     * Store a new Student Outcome.
     * Plain-English: Take the description, auto-assign the next SO code (SO1, SO2, …) and position, then save.
     */
    public function store(Request $request)
    {
        if (!$this->canManage()) {
            if ($this->wantsJson($request)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Forbidden');
        }

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        // Determine next position and code
        $nextPosition = (int) StudentOutcome::max('position') + 1;
        $nextCode     = 'SO' . $nextPosition;

        $so = StudentOutcome::create([
            'code'        => $nextCode,
            'description' => $validated['description'],
            'position'    => $nextPosition,
        ]);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => "SO '{$nextCode}' added successfully!",
                'so'      => $so,
            ], 201);
        }

        return redirect()->route('admin.master-data.index', [
            'tab'    => 'soilo',
            'subtab' => 'so',
        ])->with('success', "SO '{$nextCode}' added successfully!");
    }

    /**
     * Update an existing Student Outcome.
     * Plain-English: Edit the SO’s code and description; keep position unchanged.
     */
// 🔁 DROP-IN REPLACEMENT for update() in app/Http/Controllers/Admin/StudentOutcomeController.php
public function update(Request $request, int $id)
{
    if (!$this->canManage()) {
        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        abort(403, 'Forbidden');
    }

    // 🔧 Code is no longer editable from the modal; only validate & update description.
    $validated = $request->validate([
        'description' => 'required|string',
    ]);

    $so = StudentOutcome::findOrFail($id);
    $so->update([
        'description' => $validated['description'],
        // 'code' is intentionally not updated here; handled by reorder if needed.
    ]);

    if ($this->wantsJson($request)) {
        return response()->json([
            'message' => 'Student Outcome updated successfully!',
            'so'      => $so,
        ]);
    }

    return redirect()->route('admin.master-data.index', [
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

        return redirect()->route('admin.master-data.index', [
            'tab'    => 'soilo',
            'subtab' => 'so',
        ])->with('success', 'SO deleted successfully!');
    }

    /**
     * Reorder Student Outcomes by an ordered list of IDs.
     * Plain-English: Re-label codes (SO1…S On) and positions to match the new order.
     */
    public function reorder(Request $request): JsonResponse
    {
        if (!$this->canManage()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'orderedIds'   => 'required|array',
            'orderedIds.*' => 'integer',
        ]);

        $studentOutcomes = StudentOutcome::whereIn('id', $data['orderedIds'])
            ->get()
            ->keyBy('id');

        // Step 1: Assign temporary placeholder codes to avoid unique conflicts
        foreach ($studentOutcomes as $so) {
            $so->forceFill(['code' => '__TEMP__' . $so->id])->save();
        }

        // Step 2: Re-assign position and proper codes based on the new order
        foreach ($data['orderedIds'] as $index => $id) {
            if (isset($studentOutcomes[$id])) {
                $studentOutcomes[$id]->forceFill([
                    'position' => $index + 1,
                    'code'     => 'SO' . ($index + 1),
                ])->save();
            }
        }

        return response()->json(['message' => 'Student Outcomes reordered successfully.']);
    }

    // ░░░ END: CRUD ░░░
}
