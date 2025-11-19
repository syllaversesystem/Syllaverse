<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use App\Models\GeneralInformation;
use App\Models\Syllabus;
use App\Models\SyllabusCoursePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyllabusCoursePolicyController extends Controller
{
    /**
     * Seed course policies from general_information using department-aware lookup.
     * Lookup hierarchy: Department-specific → University default
     */
    public function seedFromGeneralInformation(Syllabus $syllabus): void
    {
        try {
            // Get department ID from the course
            $departmentId = $syllabus->course->department_id ?? null;
            
            // Define course policy sections (excluding mission and vision)
            $sections = ['policy', 'exams', 'dishonesty', 'dropping', 'other'];
            $position = 1;
            
            foreach ($sections as $section) {
                // Use department-aware lookup with fallback
                $content = GeneralInformation::getContent($section, $departmentId);
                
                // Use updateOrCreate to avoid accidental duplicates and to be idempotent
                SyllabusCoursePolicy::updateOrCreate(
                    ['syllabus_id' => $syllabus->id, 'section' => $section],
                    ['content' => $content, 'position' => $position++]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('SyllabusCoursePolicyController::seedFromGeneralInformation failed', [
                'syllabus_id' => $syllabus->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync course policies from request payload.
     */
    public function syncFromRequest(Request $request, Syllabus $syllabus): void
    {
        // Persist Course Policies from course_policies[] inputs into syllabus_course_policies
        if ($request->has('course_policies') && is_array($request->input('course_policies'))) {
            $sections = ['policy', 'exams', 'dishonesty', 'dropping', 'other'];
            $policies = $request->input('course_policies');
            
            foreach ($sections as $index => $section) {
                if (isset($policies[$index])) {
                    SyllabusCoursePolicy::updateOrCreate(
                        ['syllabus_id' => $syllabus->id, 'section' => $section],
                        ['content' => $policies[$index], 'position' => $index + 1]
                    );
                }
            }
        }
    }

    /**
     * Get all predefined course policies from general_information table based on department.
     * Uses department-aware lookup: department-specific policy → university default.
     * Returns: policy, exams, dishonesty, dropping, other (excludes mission and vision).
     */
    public function getPredefinedPolicies($id)
    {
        $syllabus = Syllabus::with('course.department')->findOrFail($id);
        
        // Get department ID from syllabus course
        $departmentId = $syllabus->course->department_id ?? null;
        
        // Define policy sections to fetch (excluding mission and vision)
        $sections = ['policy', 'exams', 'dishonesty', 'dropping', 'other'];
        $policies = [];
        $foundAny = false;
        
        // Fetch each section using department-aware lookup
        foreach ($sections as $section) {
            $content = GeneralInformation::getContent($section, $departmentId);
            if ($content) {
                $policies[$section] = $content;
                $foundAny = true;
            } else {
                $policies[$section] = '';
            }
        }
        
        if ($foundAny) {
            return response()->json([
                'success' => true,
                'policies' => $policies,
                'department_id' => $departmentId,
                'source' => $departmentId ? 'department' : 'university'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No predefined course policies found for this department or university-wide.',
            'policies' => $policies
        ], 404);
    }
}
