<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Cdio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CdioController extends Controller
{
    public function filter(Request $request)
    {
        try {
            $cdios = Cdio::ordered()->get();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'cdios' => $cdios,
                    'count' => $cdios->count(),
                ]);
            }
            return redirect()->route('faculty.dashboard');
        } catch (\Throwable $e) {
            Log::error('CDIO Filter Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load CDIO'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ]);
            $cdio = Cdio::create($validated);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'CDIO created successfully!',
                    'cdio' => $cdio,
                ], 201);
            }
            return redirect()->route('faculty.master-data.index')->with('success', 'CDIO created successfully!');
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
            Log::error('CDIO Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create CDIO'], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
            ]);
            $cdio = Cdio::findOrFail($id);
            $cdio->update($validated);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'CDIO updated successfully!',
                    'cdio' => $cdio,
                ]);
            }
            return redirect()->route('faculty.master-data.index')->with('success', 'CDIO updated successfully!');
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
            Log::error('CDIO Update Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update CDIO'], 500);
        }
    }

    public function destroy(Request $request, int $id)
    {
        try {
            $cdio = Cdio::findOrFail($id);
            $cdio->delete();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'CDIO deleted successfully!',
                    'id' => $id,
                ]);
            }
            return redirect()->route('faculty.master-data.index')->with('success', 'CDIO deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('CDIO Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete CDIO'], 500);
        }
    }
}
