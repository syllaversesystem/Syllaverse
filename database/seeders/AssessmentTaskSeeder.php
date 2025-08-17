<?php

// -----------------------------------------------------------------------------
// * File: database/seeders/AssessmentTaskSeeder.php
// * Description: Seeds default Assessment Tasks per CIS for each group
// *              (LEC: ME, FE, QCT, ARR, PR; LAB: LE, LEX).
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Initial creation – idempotent seeding keyed by (group_id, code),
//              safe re-runs, keeps created_at for existing rows.
// -----------------------------------------------------------------------------

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentTaskSeeder extends Seeder
{
    /**
     * This seeds the "assessment_tasks" table with CIS defaults for
     * Lecture (LEC) and Laboratory (LAB). Safe to run multiple times.
     */
    // ░░░ START: run() – Seed tasks ░░░
    public function run(): void
    {
        // Ensure groups exist (LEC/LAB) by invoking the group seeder first.
        $this->call(AssessmentTaskGroupSeeder::class);

        // Resolve group IDs by code
        $lecGroupId = DB::table('assessment_task_groups')->where('code', 'LEC')->value('id');
        $labGroupId = DB::table('assessment_task_groups')->where('code', 'LAB')->value('id');

        // Guard: if groups are missing, bail early to avoid errors
        if (!$lecGroupId || !$labGroupId) {
            $this->command?->warn('AssessmentTaskSeeder: LEC/LAB groups not found. Seeding skipped.');
            return;
        }

        // START: Define default task sets for each group
        $lectureTasks = [
            ['code' => 'ME',  'title' => 'Midterm Exam',                 'description' => null],
            ['code' => 'FE',  'title' => 'Final Examination',            'description' => null],
            ['code' => 'QCT', 'title' => 'Quizzes / Class Tests',        'description' => null],
            ['code' => 'ARR', 'title' => 'Assignments / Requirements',   'description' => null],
            ['code' => 'PR',  'title' => 'Project',                      'description' => null],
        ];

        $laboratoryTasks = [
            ['code' => 'LE',  'title' => 'Laboratory Exercise',          'description' => null],
            ['code' => 'LEX', 'title' => 'Laboratory Examination',       'description' => null],
        ];
        // END: Define default task sets

        $now = now();

        // Helper to upsert a set of tasks for a given group id
        $upsertTasks = function (int $groupId, array $tasks): void {
            $now = now();
            foreach ($tasks as $idx => $task) {
                $exists = DB::table('assessment_tasks')
                    ->where('group_id', $groupId)
                    ->where('code', $task['code'])
                    ->exists();

                if ($exists) {
                    DB::table('assessment_tasks')
                        ->where('group_id', $groupId)
                        ->where('code', $task['code'])
                        ->update([
                            'title'      => $task['title'],
                            'description'=> $task['description'] ?? null,
                            'sort_order' => $idx,
                            'is_active'  => true,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('assessment_tasks')->insert([
                        'group_id'   => $groupId,
                        'code'       => $task['code'],
                        'title'      => $task['title'],
                        'description'=> $task['description'] ?? null,
                        'sort_order' => $idx,
                        'is_active'  => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        };

        // Seed both sets
        $upsertTasks($lecGroupId, $lectureTasks);
        $upsertTasks($labGroupId, $laboratoryTasks);
    }
    // ░░░ END: run() – Seed tasks ░░░
}
