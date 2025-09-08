<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('tla', 'position')) {
            Schema::table('tla', function (Blueprint $table) {
                $table->integer('position')->default(0)->after('syllabus_id');
            });
        }

        // Backfill existing rows: assign positions per syllabus ordered by id
        try {
            $syllabusIds = \DB::table('tla')->select('syllabus_id')->distinct()->pluck('syllabus_id');
            foreach ($syllabusIds as $sid) {
                $rows = \DB::table('tla')->where('syllabus_id', $sid)->orderBy('id')->pluck('id');
                $pos = 0;
                foreach ($rows as $rid) {
                    \DB::table('tla')->where('id', $rid)->update(['position' => $pos++]);
                }
            }
        } catch (\Throwable $e) {
            // Swallow — best-effort backfill; leave default 0 if something fails
            \Log::warning('TLA position backfill failed: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tla', 'position')) {
            Schema::table('tla', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
    }
};
