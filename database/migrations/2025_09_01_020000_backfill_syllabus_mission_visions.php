<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void
    {
        // If the general_informations table doesn't exist yet (migration order), skip backfill.
        if (!Schema::hasTable('general_informations')) {
            return;
        }

        // Fetch global mission/vision from GeneralInformation (if present)
        $mission = DB::table('general_informations')->where('section', 'mission')->value('content') ?? '';
        $vision  = DB::table('general_informations')->where('section', 'vision')->value('content') ?? '';

        $now = Carbon::now()->toDateTimeString();

        $syllabusIds = DB::table('syllabi')->pluck('id');
        foreach ($syllabusIds as $id) {
            // only insert if missing
            $exists = DB::table('syllabus_mission_visions')->where('syllabus_id', $id)->exists();
            if (!$exists) {
                DB::table('syllabus_mission_visions')->insert([
                    'syllabus_id' => $id,
                    'mission' => $mission,
                    'vision' => $vision,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // no-op: keep mission/vision data
    }
};
