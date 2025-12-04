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
use Illuminate\Support\Facades\Schema;

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

            // Reviewers: canonical CHAIR only, department-scoped
            $reviewersQuery = User::whereHas('appointments', function($query) use ($departmentId) {
                $query->where('status', 'active')
                      ->where('scope_id', $departmentId)
                      ->where('role', Appointment::ROLE_CHAIR);
            })
            ->with(['appointments' => function($query) use ($departmentId) {
                $query->where('status', 'active')
                      ->where('scope_id', $departmentId)
                      ->where('role', Appointment::ROLE_CHAIR);
            }])
            ->get();

            Log::info('[Reviewers] query result', [
                'count' => $reviewersQuery->count(),
                'ids'   => $reviewersQuery->pluck('id')
            ]);

            // Decide label based on number of programs in the department
            // Label for CHAIR depends on program count in the department
            $programCount = DB::table('programs')->where('department_id', $departmentId)->count();
            $labelForDept = ($programCount >= 2) ? 'Department Chairperson' : 'Program Chairperson';

            $reviewers = $reviewersQuery
            ->map(function($user) use ($labelForDept) {
                $appointment = $user->appointments->first();
                $roleLabel = $labelForDept;
                $roleValue = $appointment->role ?? Appointment::ROLE_CHAIR;
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $roleValue,
                    'role_label' => $roleLabel,
                ];
            });

            // No fallback: reviewers must be canonical CHAIR

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

            // Final approvers: canonical DEPT_HEAD and ASSOC_DEAN, both department-scoped
            $approvers = User::whereHas('appointments', function($query) use ($departmentId) {
                    $query->where('status', 'active')
                          ->where('scope_id', $departmentId)
                          ->whereIn('role', [Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_ASSOC_DEAN]);
                })
                ->with(['appointments' => function($query) use ($departmentId) {
                    $query->where('status', 'active')
                          ->where('scope_id', $departmentId)
                          ->whereIn('role', [Appointment::ROLE_DEPT_HEAD, Appointment::ROLE_ASSOC_DEAN]);
                }])
                ->get()
                ->map(function($user) use ($departmentId) {
                    $appointment = $user->appointments->first();
                    // Determine Department Head label based on department classification
                    $dept = DB::table('departments')->where('id', $departmentId)->first();
                    $deptName = $dept->name ?? '';
                    $deptHeadLabel = 'Dean';
                    // Heuristics: Lab School → Principal; General Education → Head; else Dean
                    if (is_string($deptName)) {
                        $lower = strtolower($deptName);
                        if (str_contains($lower, 'lab') || str_contains($lower, 'school')) {
                            $deptHeadLabel = 'Principal';
                        } elseif (str_contains($lower, 'general education')) {
                            $deptHeadLabel = 'Head';
                        } else {
                            $deptHeadLabel = 'Dean';
                        }
                    }
                    $roleLabel = ($appointment && $appointment->role === Appointment::ROLE_DEPT_HEAD) ? $deptHeadLabel : 'Associate Dean';
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
                    // Stamp approval signature fields if provided
                    // Only set the numeric approver id if the column exists to avoid SQL errors
                    try {
                        if (Schema::hasColumn('syllabi', 'approved_by')) {
                            $syllabus->setAttribute('approved_by', $approverId);
                        }
                    } catch (\Throwable $t) {
                        // ignore schema check issues
                    }
                    $syllabus->approved_by_name = $approver->name;
                    // Derive approver title (Dean / Associate Dean) when possible
                    try {
                        $deptId = optional($syllabus->program)->department_id;
                        $appt = $approver->appointments()
                            ->where('status', 'active')
                            ->where(function($q) use ($deptId) {
                                $q->where('role', Appointment::ROLE_DEAN)
                                  ->orWhere(function($qq) use ($deptId) {
                                      $qq->where('role', Appointment::ROLE_ASSOC_DEAN);
                                      if ($deptId) { $qq->where('scope_id', $deptId); }
                                  });
                            })
                            ->first();
                        if ($appt) {
                            $syllabus->approved_by_title = $appt->role === Appointment::ROLE_DEAN ? 'Dean' : 'Associate Dean';
                        }
                    } catch (\Throwable $t) {
                        // non-fatal if we cannot infer title
                    }
                    try {
                        $syllabus->approved_by_date = now()->format('Y-m-d');
                    } catch (\Throwable $t) {
                        $syllabus->approved_by_date = date('Y-m-d');
                    }
                    // Assign final approver to reviewed_by so Approvals list can filter by user
                    $syllabus->reviewed_by = $approverId;
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
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            $syllabus = Syllabus::findOrFail($syllabusId);

            $remarks = $request->input('remarks');

            // Branch by current submission status
            if ($syllabus->submission_status === 'pending_review') {
                // Only the assigned reviewer can approve when status is pending_review
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

                DB::beginTransaction();

                $fromStatus = $syllabus->submission_status;
                $syllabus->submission_status = 'approved';
                $syllabus->submission_remarks = $remarks;
                // Stamp the Reviewed date so the status partial reflects approval time
                try { $syllabus->reviewed_by_date = now()->format('Y-m-d'); }
                catch (\Throwable $t) { $syllabus->reviewed_by_date = date('Y-m-d'); }
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

                return response()->json(['success' => true, 'message' => 'Syllabus approved successfully']);
            }

            if ($syllabus->submission_status === 'final_approval') {
                // Only the assigned final approver (Dean / Associate Dean) can finalize
                if ((int)$syllabus->reviewed_by !== (int)$user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not the assigned final approver for this syllabus'
                    ], 403);
                }

                DB::beginTransaction();
                $fromStatus = $syllabus->submission_status;

                // Stamp Approved By block based on current approver
                try {
                    // Optionally set numeric approved_by if the column exists
                    if (Schema::hasColumn('syllabi', 'approved_by')) {
                        $syllabus->setAttribute('approved_by', $user->id);
                    }
                } catch (\Throwable $t) { /* ignore schema check errors */ }
                $syllabus->approved_by_name = $user->name;
                // Infer approver title from appointments
                try {
                    $deptId = optional($syllabus->program)->department_id;
                    $appt = $user->appointments()
                        ->where('status', 'active')
                        ->where(function($q) use ($deptId) {
                            $q->where('role', Appointment::ROLE_DEAN)
                              ->orWhere(function($qq) use ($deptId) {
                                  $qq->where('role', Appointment::ROLE_ASSOC_DEAN);
                                  if ($deptId) { $qq->where('scope_id', $deptId); }
                              });
                        })
                        ->first();
                    if ($appt) {
                        $syllabus->approved_by_title = $appt->role === Appointment::ROLE_DEAN ? 'Dean' : 'Associate Dean';
                    }
                } catch (\Throwable $t) { /* ignore */ }
                try { $syllabus->approved_by_date = now()->format('Y-m-d'); }
                catch (\Throwable $t) { $syllabus->approved_by_date = date('Y-m-d'); }

                // Transition to final_approved so it no longer appears in Approvals
                $syllabus->submission_status = 'final_approved';
                $syllabus->submission_remarks = $remarks;
                $syllabus->save();

                // History record
                SyllabusSubmission::create([
                    'syllabus_id' => $syllabus->id,
                    'submitted_by' => $syllabus->faculty_id ?: $user->id,
                    'from_status' => $fromStatus,
                    'to_status' => 'final_approved',
                    'action_by' => $user->id,
                    'remarks' => $remarks,
                    'action_at' => now(),
                ]);

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Syllabus final-approved successfully']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Syllabus is not in an approvable state'
            ], 400);
        } catch (\Throwable $e) {
            // Ensure we roll back any open transaction
            try { DB::rollBack(); } catch (\Throwable $__) {}
            Log::error('Error approving syllabus', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
