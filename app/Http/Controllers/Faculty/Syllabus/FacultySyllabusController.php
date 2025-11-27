<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Syllabus;

class FacultySyllabusController extends Controller
{
    /**
     * Create faculty-syllabus relationships based on recipient type
     *
     * @param Syllabus $syllabus
     * @param string $recipientType (myself|shared|others)
     * @param array $facultyMembers
     * @return void
     */
    public function createRelationships(Syllabus $syllabus, string $recipientType, array $facultyMembers = [])
    {
        if ($recipientType === 'myself') {
            // Current user is sole owner
            $this->addRelationship($syllabus->id, Auth::id(), 'owner', true);
        } elseif ($recipientType === 'shared') {
            // Current user is owner
            $this->addRelationship($syllabus->id, Auth::id(), 'owner', true);
            
            // Others are collaborators
            foreach ($facultyMembers as $facultyId) {
                if ($facultyId != Auth::id()) {
                    $this->addRelationship($syllabus->id, $facultyId, 'collaborator', true);
                }
            }
        } elseif ($recipientType === 'others') {
            // Current user is owner (creating on behalf of others)
            $this->addRelationship($syllabus->id, Auth::id(), 'owner', true);
            
            // Selected faculty are collaborators
            if (!empty($facultyMembers)) {
                foreach ($facultyMembers as $facultyId) {
                    if ($facultyId != Auth::id()) {
                        $this->addRelationship($syllabus->id, $facultyId, 'collaborator', true);
                    }
                }
            }
        }
    }

    /**
     * Add a single faculty-syllabus relationship
     *
     * @param int $syllabusId
     * @param int $facultyId
     * @param string $role (owner|collaborator|viewer)
     * @param bool $canEdit
     * @return void
     */
    protected function addRelationship(int $syllabusId, int $facultyId, string $role, bool $canEdit)
    {
        DB::table('faculty_syllabus')->insert([
            'faculty_id' => $facultyId,
            'syllabus_id' => $syllabusId,
            'role' => $role,
            'can_edit' => $canEdit,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get user's role for a specific syllabus
     *
     * @param int $syllabusId
     * @param int|null $facultyId
     * @return string|null (owner|collaborator|viewer)
     */
    public function getUserRole(int $syllabusId, ?int $facultyId = null): ?string
    {
        $facultyId = $facultyId ?? Auth::id();
        
        $relationship = DB::table('faculty_syllabus')
            ->where('syllabus_id', $syllabusId)
            ->where('faculty_id', $facultyId)
            ->first();

        return $relationship?->role;
    }

    /**
     * Check if user can edit a syllabus
     *
     * @param int $syllabusId
     * @param int|null $facultyId
     * @return bool
     */
    public function canEdit(int $syllabusId, ?int $facultyId = null): bool
    {
        $facultyId = $facultyId ?? Auth::id();
        
        $relationship = DB::table('faculty_syllabus')
            ->where('syllabus_id', $syllabusId)
            ->where('faculty_id', $facultyId)
            ->first();

        return $relationship?->can_edit ?? false;
    }

    /**
     * Get all faculty members for a syllabus
     *
     * @param int $syllabusId
     * @return \Illuminate\Support\Collection
     */
    public function getSyllabusMembers(int $syllabusId)
    {
        return DB::table('faculty_syllabus')
            ->join('users', 'faculty_syllabus.faculty_id', '=', 'users.id')
            ->where('faculty_syllabus.syllabus_id', $syllabusId)
            ->select('users.*', 'faculty_syllabus.role', 'faculty_syllabus.can_edit')
            ->orderByRaw("FIELD(faculty_syllabus.role, 'owner', 'collaborator', 'viewer')")
            ->get();
    }

    /**
     * Add a faculty member to a syllabus
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addMember(Request $request)
    {
        $request->validate([
            'syllabus_id' => 'required|exists:syllabi,id',
            'faculty_id' => 'required|exists:users,id',
            'role' => 'required|in:collaborator,viewer',
            'can_edit' => 'boolean',
        ]);

        // Check if current user is owner
        if ($this->getUserRole($request->syllabus_id) !== 'owner') {
            return response()->json(['message' => 'Only owners can add members'], 403);
        }

        // Check if relationship already exists
        $exists = DB::table('faculty_syllabus')
            ->where('syllabus_id', $request->syllabus_id)
            ->where('faculty_id', $request->faculty_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Faculty member already has access'], 409);
        }

        $this->addRelationship(
            $request->syllabus_id,
            $request->faculty_id,
            $request->role,
            $request->can_edit ?? ($request->role === 'collaborator')
        );

        return response()->json(['message' => 'Faculty member added successfully']);
    }

    /**
     * Update a faculty member's role/permissions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMember(Request $request)
    {
        $request->validate([
            'syllabus_id' => 'required|exists:syllabi,id',
            'faculty_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:collaborator,viewer',
            'can_edit' => 'sometimes|boolean',
        ]);

        // Check if current user is owner
        if ($this->getUserRole($request->syllabus_id) !== 'owner') {
            return response()->json(['message' => 'Only owners can update members'], 403);
        }

        $updateData = ['updated_at' => now()];
        if ($request->has('role')) {
            $updateData['role'] = $request->role;
        }
        if ($request->has('can_edit')) {
            $updateData['can_edit'] = $request->can_edit;
        }

        DB::table('faculty_syllabus')
            ->where('syllabus_id', $request->syllabus_id)
            ->where('faculty_id', $request->faculty_id)
            ->update($updateData);

        return response()->json(['message' => 'Member updated successfully']);
    }

    /**
     * Remove a faculty member from a syllabus
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeMember(Request $request)
    {
        $request->validate([
            'syllabus_id' => 'required|exists:syllabi,id',
            'faculty_id' => 'required|exists:users,id',
        ]);

        // Check if current user is owner
        if ($this->getUserRole($request->syllabus_id) !== 'owner') {
            return response()->json(['message' => 'Only owners can remove members'], 403);
        }

        // Prevent removing the owner
        $targetRole = $this->getUserRole($request->syllabus_id, $request->faculty_id);
        if ($targetRole === 'owner') {
            return response()->json(['message' => 'Cannot remove the owner'], 403);
        }

        DB::table('faculty_syllabus')
            ->where('syllabus_id', $request->syllabus_id)
            ->where('faculty_id', $request->faculty_id)
            ->delete();

        return response()->json(['message' => 'Member removed successfully']);
    }
}
