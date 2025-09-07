<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MergeDisabilityAdvisingIntoOtherPolicies extends Migration
{
    /**
     * Run the migrations.
    * Merge per-syllabus `disability` and `advising` rows into `other`.
    * - If `other` exists, its content will be replaced by a combined
    *   value (other + advising + disability) with double-newline separators.
    * - If it does not exist, a new `other` row is inserted.
     * - Legacy rows (`disability`, `advising`) are deleted afterwards.
     */
    public function up()
    {
        if (!Schema::hasTable('syllabus_course_policies')) {
            return;
        }

        $legacy = ['disability', 'advising'];

        $sylIds = DB::table('syllabus_course_policies')
            ->whereIn('section', $legacy)
            ->pluck('syllabus_id')
            ->unique()
            ->values();

        if ($sylIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($sylIds, $legacy) {
            foreach ($sylIds as $sylId) {
                $rows = DB::table('syllabus_course_policies')
                    ->where('syllabus_id', $sylId)
                    ->whereIn('section', array_merge($legacy, ['other']))
                    ->orderBy('position')
                    ->get()
                    ->keyBy('section');

                $parts = [];
                if (isset($rows['other']) && trim($rows['other']->content) !== '') {
                    $parts[] = trim($rows['other']->content);
                }
                if (isset($rows['advising']) && trim($rows['advising']->content) !== '') {
                    $parts[] = trim($rows['advising']->content);
                }
                if (isset($rows['disability']) && trim($rows['disability']->content) !== '') {
                    $parts[] = trim($rows['disability']->content);
                }

                $combined = trim(implode("\n\n", $parts));

                if ($combined === '') {
                    // Nothing meaningful to keep; remove empty legacy rows to tidy the table.
                    DB::table('syllabus_course_policies')
                        ->where('syllabus_id', $sylId)
                        ->whereIn('section', $legacy)
                        ->delete();
                    continue;
                }

                if (isset($rows['other'])) {
                    // update existing other
                    DB::table('syllabus_course_policies')
                        ->where('id', $rows['other']->id)
                        ->update(['content' => $combined, 'updated_at' => Carbon::now()]);
                } else {
                    // determine a reasonable position: prefer legacy minimum position or append
                    $positions = collect($rows)->pluck('position')->filter()->values();
                    $pos = $positions->min() ?? (DB::table('syllabus_course_policies')->where('syllabus_id', $sylId)->max('position') + 1);

                    DB::table('syllabus_course_policies')->insert([
                        'syllabus_id' => $sylId,
                        'section'     => 'other',
                        'content'     => $combined,
                        'position'    => $pos,
                        'created_at'  => Carbon::now(),
                        'updated_at'  => Carbon::now(),
                    ]);
                }

                // remove legacy rows after consolidation
                DB::table('syllabus_course_policies')
                    ->where('syllabus_id', $sylId)
                    ->whereIn('section', $legacy)
                    ->delete();
            }
        });
    }

    /**
     * Reverse the migrations.
     * This operation is destructive (merges content) and cannot reliably be reversed.
     * We'll leave down() as a no-op to avoid accidental data loss on rollbacks.
     */
    public function down()
    {
        // Intentionally left blank - non-reversible migration.
    }
}
