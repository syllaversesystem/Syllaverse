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
            $igas = Iga::ordered()->get();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'igas' => $igas,
                    'count' => $igas->count(),
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
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ]);

            $iga = Iga::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
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
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ]);

            $iga = Iga::findOrFail($id);

            $iga->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);

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
            $iga = Iga::findOrFail($id);
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
