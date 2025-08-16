<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/ProgramController.php
// * Description: Handles create, update, and delete of Programs (Admin - Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Added AJAX support: returns JSON when expectsJson().
// [2025-08-18] Synced with MasterDataController – delete now returns ID, consistent payloads.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Program;

class ProgramController extends Controller
{
    /**
     * Store a new program.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 🔎 Ensure user is a Department Chair
        $deptAppt = $user->appointments()
            ->active()
            ->where('role', \App\Models\Appointment::ROLE_DEPT)
            ->first();

        if (!$deptAppt) {
            return $request->expectsJson()
                ? response()->json(['error' => 'You must be a Department Chair to add programs.'], 403)
                : back()->with('error', 'You must be a Department Chair to add programs.');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:25|unique:programs,code',
            'description' => 'nullable|string',
        ]);

        $program = Program::create([
            'name'          => $validated['name'],
            'code'          => $validated['code'],
            'description'   => $validated['description'] ?? null,
            'department_id' => $deptAppt->scope_id,
            'created_by'    => $user->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Program added successfully!',
                'program' => $program,
            ]);
        }

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'programs',
        ])->with('success', 'Program added successfully!');
    }

    /**
     * Update an existing program.
     */
    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);
        $user = Auth::user();

        // 🔒 Check authority (dept or program chair)
        $hasAuthority = $user->appointments()
            ->active()
            ->get()
            ->contains(fn($appt) => $appt->coversProgram($program));

        if (!$hasAuthority) {
            return $request->expectsJson()
                ? response()->json(['error' => 'You do not have permission to update this program.'], 403)
                : back()->with('error', 'You do not have permission to update this program.');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:25|unique:programs,code,' . $program->id,
            'description' => 'nullable|string',
        ]);

        $program->update([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Program updated successfully!',
                'program' => $program,
            ]);
        }

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'programs',
        ])->with('success', 'Program updated successfully!');
    }

    /**
     * Delete a program.
     */
    public function destroy(Request $request, $id)
    {
        $program = Program::findOrFail($id);
        $program->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Program deleted successfully!',
                'id'      => $id,
            ]);
        }

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'programs',
        ])->with('success', 'Program deleted successfully!');
    }
}
