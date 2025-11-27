<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SyllabusStatusController extends Controller
{
    /**
     * Save syllabus status fields (Prepared/Reviewed/Approved signatures)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        try {
            $validated = $request->validate([
                'syllabus_id' => 'required|exists:syllabi,id',
                'prepared_by_name' => 'nullable|string|max:255',
                'prepared_by_title' => 'nullable|string|max:255',
                'prepared_by_date' => 'nullable|date',
                'reviewed_by_name' => 'nullable|string|max:255',
                'reviewed_by_title' => 'nullable|string|max:255',
                'reviewed_by_date' => 'nullable|date',
                'approved_by_name' => 'nullable|string|max:255',
                'approved_by_title' => 'nullable|string|max:255',
                'approved_by_date' => 'nullable|date',
                'status_remarks' => 'nullable|string',
            ]);

            $syllabus = Syllabus::where('id', $validated['syllabus_id'])
                ->where('faculty_id', Auth::id())
                ->firstOrFail();

            $syllabus->update([
                'prepared_by_name' => $validated['prepared_by_name'] ?? null,
                'prepared_by_title' => $validated['prepared_by_title'] ?? null,
                'prepared_by_date' => $validated['prepared_by_date'] ?? null,
                'reviewed_by_name' => $validated['reviewed_by_name'] ?? null,
                'reviewed_by_title' => $validated['reviewed_by_title'] ?? null,
                'reviewed_by_date' => $validated['reviewed_by_date'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'approved_by_title' => $validated['approved_by_title'] ?? null,
                'approved_by_date' => $validated['approved_by_date'] ?? null,
                'status_remarks' => $validated['status_remarks'] ?? null,
            ]);

            Log::info('Syllabus status updated', [
                'syllabus_id' => $syllabus->id,
                'faculty_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Syllabus status saved successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Syllabus not found or access denied.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to save syllabus status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save syllabus status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync status fields from request (called by main SyllabusController)
     *
     * @param Request $request
     * @param Syllabus $syllabus
     * @return void
     */
    public function syncFromRequest(Request $request, Syllabus $syllabus): void
    {
        $syllabus->update($request->only([
            'prepared_by_name',
            'prepared_by_title',
            'prepared_by_date',
            'reviewed_by_name',
            'reviewed_by_title',
            'reviewed_by_date',
            'approved_by_name',
            'approved_by_title',
            'approved_by_date',
            'status_remarks'
        ]));
    }
}
