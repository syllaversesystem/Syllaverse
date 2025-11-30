<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\SyllabusCourseInfo;
use App\Models\SyllabusSubmission;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyllabusSubmissionController extends Controller
{
    /**
     * Get available reviewers (Program/Department Chairperson) for a syllabus
     */
    public function getReviewers(Request $request, $syllabusId)
    {
        try {
            $departmentId = $request->query('department_id');
            $programId    = $request->query('program_id');

            Log::info('[Reviewers] incoming params', [
                'syllabus_id'   => $syllabusId,
                'department_id' => $departmentId,
                'program_id'    => $programId,
            ]);
            
            if (!$departmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department ID is required',
                    'reviewers' => []
                ], 400);
            }

            // Get users who are Department Chairs for the department and Program Chairs for the program (if provided)
            // Be tolerant of scope_type inconsistencies: match by role and scope_id primarily.
            // Business rule: Chair role is department-scoped. Label depends on program count in department.
            // Allow both legacy dept chair and new dept head role identifiers
            $chairRoles = [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD];
            $reviewersQuery = User::whereHas('appointments', function($query) use ($departmentId, $chairRoles) {
                $query->where('status', 'active')
                      ->where('scope_id', $departmentId)
                      ->whereIn('role', $chairRoles);
            })
            ->with(['appointments' => function($query) use ($departmentId, $chairRoles) {
                $query->where('status', 'active')
                      ->where('scope_id', $departmentId)
                      ->whereIn('role', $chairRoles);
            }])
            ->get();

            Log::info('[Reviewers] query result', [
                'count' => $reviewersQuery->count(),
                'ids'   => $reviewersQuery->pluck('id')
            ]);

            // Decide label based on number of programs in the department
            $programCount = DB::table('programs')->where('department_id', $departmentId)->count();
            $labelForDept = $programCount <= 1 ? 'Program Chairperson' : 'Department Chairperson';

            $reviewers = $reviewersQuery
            ->map(function($user) use ($labelForDept) {
                $appointment = $user->appointments->first();
                $roleLabel = $labelForDept;
                $roleValue = $appointment->role ?? Appointment::ROLE_DEPT;
                // Normalize role string for frontend (treat DEPT_HEAD equivalent to DEPT_CHAIR)
                if ($roleValue === Appointment::ROLE_DEPT_HEAD) {
                    $roleValue = Appointment::ROLE_DEPT; // unify legacy expectation
                }
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $roleValue,
                    'role_label' => $roleLabel,
                ];
            });

            // Auto-fallback: if none found for department, show all active Program Chairs across institution
            if ($reviewers->isEmpty()) {
                $fallback = User::whereHas('appointments', function($query) {
                        $query->where('status', 'active')
                              ->where('role', Appointment::ROLE_PROG);
                    })
                    ->with(['appointments' => function($query) {
                        $query->where('status', 'active')
                              ->where('role', Appointment::ROLE_PROG);
                    }])
                    ->get()
                    ->map(function($user) {
                        $appointment = $user->appointments->first();
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $appointment->role ?? Appointment::ROLE_PROG,
                            'role_label' => 'Program Chairperson',
                        ];
                    });

                if ($fallback->isNotEmpty()) {
                    $reviewers = $fallback;
                }
            }

            return response()->json([
                'success' => true,
                'reviewers' => $reviewers
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reviewers', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching reviewers',
                'reviewers' => []
            ], 500);
        }
    }

    /**
     * Get available final approvers (Dean / Associate Dean)
     */
    public function getFinalApprovers(Request $request, $syllabusId)
    {
        try {
            $departmentId = $request->query('department_id');

            if (!$departmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department ID is required',
                    'approvers' => []
                ], 400);
            }

            // Deans: institution-scoped; Associate Deans: department-scoped
            $approvers = User::whereHas('appointments', function($query) use ($departmentId) {
                    $query->where('status', 'active')
                          ->where(function($q) use ($departmentId) {
                              $q->where('role', Appointment::ROLE_DEAN)
                                ->orWhere(function($qq) use ($departmentId) {
                                    $qq->where('role', Appointment::ROLE_ASSOC_DEAN)
                                       ->where('scope_id', $departmentId);
                                });
                          });
                })
                ->with(['appointments' => function($query) use ($departmentId) {
                    $query->where('status', 'active')
                          ->where(function($q) use ($departmentId) {
                              $q->where('role', Appointment::ROLE_DEAN)
                                ->orWhere(function($qq) use ($departmentId) {
                                    $qq->where('role', Appointment::ROLE_ASSOC_DEAN)
                                       ->where('scope_id', $departmentId);
                                });
                          });
                }])
                ->get()
                ->map(function($user) {
                    $appointment = $user->appointments->first();
                    $roleLabel = ($appointment && $appointment->role === Appointment::ROLE_DEAN) ? 'Dean' : 'Associate Dean';
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $appointment->role ?? null,
                        'role_label' => $roleLabel,
                    ];
                });

            return response()->json([
                'success' => true,
                'approvers' => $approvers
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching final approvers', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching final approvers',
                'approvers' => []
            ], 500);
        }
    }

    /**
     * Submit syllabus for review or final approval
     */
    public function submit(Request $request, $syllabusId)
    {
        try {
            $syllabus = Syllabus::findOrFail($syllabusId);
            
            // Verify user has permission to submit
            $user = auth()->user();
            if ($syllabus->faculty_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to submit this syllabus'
                ], 403);
            }

            $actionType = $request->input('action_type'); // 'review' or 'final_approval'
            $remarks = $request->input('remarks');
            
            DB::beginTransaction();
            
            if ($actionType === 'review') {
                // Submit for review (draft/revision -> pending_review)
                $request->validate([
                    'reviewer_id' => 'required|exists:users,id'
                ]);
                
                $reviewerId = $request->input('reviewer_id');
                $fromStatus = $syllabus->submission_status;
                
                // Update syllabus status
                $syllabus->submission_status = 'pending_review';
                $syllabus->submission_remarks = $remarks;
                $syllabus->submitted_at = now();
                $syllabus->reviewed_by = $reviewerId;
                $syllabus->save();
                
                // Create submission history record
                SyllabusSubmission::create([
                    'syllabus_id' => $syllabus->id,
                    'submitted_by' => $user->id,
                    'from_status' => $fromStatus,
                    'to_status' => 'pending_review',
                    'action_by' => $user->id,
                    'remarks' => $remarks,
                    'action_at' => now(),
                ]);
                
                $message = 'Syllabus submitted for review';
                
            } elseif ($actionType === 'final_approval') {
                // Submit for final approval (approved -> final_approval)
                if ($syllabus->submission_status !== 'approved') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Syllabus must be approved before submitting for final approval'
                    ], 400);
                }
                
                $fromStatus = $syllabus->submission_status;

                // Optional approver selection (Dean / Associate Dean)
                $approverId = $request->input('approver_id');
                if ($approverId) {
                    $approver = User::find($approverId);
                    if (!$approver) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected approver not found'
                        ], 400);
                    }
                    // Stamp approved_by fields if provided
                    $syllabus->approved_by = $approverId;
                    $syllabus->approved_by_name = $approver->name;
                    try {
                        $syllabus->approved_by_date = now()->format('Y-m-d');
                    } catch (\Throwable $t) {
                        $syllabus->approved_by_date = date('Y-m-d');
                    }
                }
                
                // Update syllabus status
                $syllabus->submission_status = 'final_approval';
                $syllabus->submission_remarks = $remarks;
                $syllabus->save();
                
                // Create submission history record
                SyllabusSubmission::create([
                    'syllabus_id' => $syllabus->id,
                    'submitted_by' => $user->id,
                    'from_status' => $fromStatus,
                    'to_status' => 'final_approval',
                    'action_by' => $user->id,
                    'remarks' => $remarks,
                    'action_at' => now(),
                ]);
                
                $message = 'Syllabus submitted for final approval';
                
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action type'
                ], 400);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error submitting syllabus', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting the syllabus'
            ], 500);
        }
    }

    /**
     * Approve a syllabus currently in pending_review by the assigned reviewer
     */
    public function approve(Request $request, $syllabusId)
    {
        try {
            $user = auth()->user();
            $syllabus = Syllabus::findOrFail($syllabusId);

            // Only the assigned reviewer can approve when status is pending_review
            if ($syllabus->submission_status !== 'pending_review') {
                return response()->json([
                    'success' => false,
                    'message' => 'Syllabus is not pending review'
                ], 400);
            }
            // If no reviewer is assigned yet, claim it to the current reviewer
            if (empty($syllabus->reviewed_by)) {
                $syllabus->reviewed_by = $user->id;
                try { $syllabus->save(); } catch (\Throwable $t) {}
            }
            if ((int)$syllabus->reviewed_by !== (int)$user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not the assigned reviewer for this syllabus'
                ], 403);
            }

            $remarks = $request->input('remarks');

            DB::beginTransaction();

            $fromStatus = $syllabus->submission_status;
            $syllabus->submission_status = 'approved';
            $syllabus->submission_remarks = $remarks;
            // Stamp the Reviewed date so the status partial reflects approval time
            try {
                $syllabus->reviewed_by_date = now()->format('Y-m-d');
            } catch (\Throwable $t) {
                $syllabus->reviewed_by_date = date('Y-m-d');
            }
            $syllabus->save();

            // Determine submitted_by safely when faculty_id may be null
            $submittedByApprove = $syllabus->faculty_id
                ?: optional($syllabus->facultyMembers()->wherePivot('role', 'owner')->first())->id
                ?: optional($syllabus->facultyMembers()->first())->id
                ?: $user->id;

            SyllabusSubmission::create([
                'syllabus_id' => $syllabus->id,
                'submitted_by' => $submittedByApprove,
                'from_status' => $fromStatus,
                'to_status' => 'approved',
                'action_by' => $user->id,
                'remarks' => $remarks,
                'action_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Syllabus approved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving syllabus', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the syllabus'
            ], 500);
        }
    }

    /**
     * Request revision on a syllabus currently in pending_review by the assigned reviewer
     */
    public function requestRevision(Request $request, $syllabusId)
    {
        try {
            $user = auth()->user();
            $syllabus = Syllabus::findOrFail($syllabusId);

            if ($syllabus->submission_status !== 'pending_review') {
                return response()->json([
                    'success' => false,
                    'message' => 'Syllabus is not pending review'
                ], 400);
            }
            if ((int)$syllabus->reviewed_by !== (int)$user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not the assigned reviewer for this syllabus'
                ], 403);
            }

            $request->validate([
                'remarks' => 'nullable|string|max:2000'
            ]);
            $remarks = $request->input('remarks');

            DB::beginTransaction();

            $fromStatus = $syllabus->submission_status;
            $syllabus->submission_status = 'revision';
            $syllabus->submission_remarks = $remarks;
            $syllabus->save();

            // Auto-increment revision number and stamp revision date in Course Info
            try {
                /** @var SyllabusCourseInfo|null $ci */
                $ci = $syllabus->courseInfo()->lockForUpdate()->first();
                if (!$ci) {
                    $ci = new SyllabusCourseInfo();
                    $ci->syllabus_id = $syllabus->id;
                    $ci->revision_no = 1;
                } else {
                    $ci->revision_no = (int)($ci->revision_no ?? 0) + 1;
                }
                try { $ci->revision_date = now()->format('Y-m-d'); }
                catch (\Throwable $t) { $ci->revision_date = date('Y-m-d'); }
                $ci->save();
            } catch (\Throwable $t) {
                \Log::warning('Failed to update course info revision metadata', [
                    'syllabus_id' => $syllabus->id,
                    'error' => $t->getMessage(),
                ]);
            }

            // Determine submitted_by safely when faculty_id may be null
            $submittedBy = $syllabus->faculty_id
                ?: optional($syllabus->facultyMembers()->wherePivot('role', 'owner')->first())->id
                ?: optional($syllabus->facultyMembers()->first())->id
                ?: $user->id;

            SyllabusSubmission::create([
                'syllabus_id' => $syllabus->id,
                'submitted_by' => $submittedBy,
                'from_status' => $fromStatus,
                'to_status' => 'revision',
                'action_by' => $user->id,
                'remarks' => $remarks,
                'action_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Revision requested successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error requesting revision', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while requesting revision: '.$e->getMessage()
            ], 500);
        }
    }
}
