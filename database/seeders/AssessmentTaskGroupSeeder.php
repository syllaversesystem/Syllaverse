<?php

// -----------------------------------------------------------------------------
// * File: database/seeders/AssessmentTaskGroupSeeder.php
// * Description: Seeds assessment task groups (LEC = Lecture, LAB = Laboratory)
// *              used to organize assessment tasks into subtabs.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Initial creation – idempotent seeding for LEC/LAB with slugs,
//              sort order, and active flag. Uses raw DB upsert to preserve timestamps.
// -----------------------------------------------------------------------------

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssessmentTaskGroupSeeder extends Seeder
{
    /**
     * This seeds the "assessment_task_groups" table with the two CIS groups:
     * LEC (Lecture) and LAB (Laboratory). Safe to run multiple times.
     */
    // ░░░ START: run() – Seed groups ░░░
    public function run(): void
    {
        // START: Data payload
        $groups = [
            [
                'code'       => 'LEC',
                'title'      => 'Lecture',
                'slug'       => Str::slug('Lecture'),
                'sort_order' => 0,
                'is_active'  => true,
            ],
            [
                'code'       => 'LAB',
                'title'      => 'Laboratory',
                'slug'       => Str::slug('Laboratory'),
                'sort_order' => 1,
                'is_active'  => true,
            ],
        ];
        // END: Data payload

        $now = now();

        // START: Upsert loop (preserve created_at on existing rows)
        foreach ($groups as $g) {
            $exists = DB::table('assessment_task_groups')
                ->where('code', $g['code'])
                ->exists();

            if ($exists) {
                DB::table('assessment_task_groups')
                    ->where('code', $g['code'])
                    ->update([
                        'title'      => $g['title'],
                        'slug'       => $g['slug'],
                        'sort_order' => $g['sort_order'],
                        'is_active'  => $g['is_active'],
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('assessment_task_groups')->insert([
                    'code'       => $g['code'],
                    'title'      => $g['title'],
                    'slug'       => $g['slug'],
                    'sort_order' => $g['sort_order'],
                    'is_active'  => $g['is_active'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
        // END: Upsert loop
    }
    // ░░░ END: run() – Seed groups ░░░
}
