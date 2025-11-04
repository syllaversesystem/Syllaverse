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
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ]);

            $sdg = Sdg::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);

            // No department on SDG

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
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ]);

            $sdg = Sdg::findOrFail($id);
            $payload = [ 'title' => $validated['title'], 'description' => $validated['description'] ];

            $sdg->update($payload);
            // No department on SDG

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
            // SDG has no department; ignore department filter
            $sdgs = Sdg::ordered()->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'sdgs' => $sdgs,
                    'count' => $sdgs->count(),
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
