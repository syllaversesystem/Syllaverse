<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop unique index on sdgs.code first (if exists), then drop the column
        try {
            $idx = DB::select("SHOW INDEX FROM `sdgs` WHERE Key_name = 'sdgs_code_unique'");
            if (!empty($idx)) {
                DB::statement("ALTER TABLE `sdgs` DROP INDEX `sdgs_code_unique`");
            }
        } catch (\Throwable $e) {
            // ignore
        }

        if (Schema::hasColumn('sdgs', 'code')) {
            Schema::table('sdgs', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the `code` column and attempt to backfill from sort_order (or sequential by id)
        if (!Schema::hasColumn('sdgs', 'code')) {
            Schema::table('sdgs', function (Blueprint $table) {
                $table->string('code', 32)->nullable()->after('id');
            });
        }

        // Backfill codes
        $hasSortOrder = Schema::hasColumn('sdgs', 'sort_order');
        $rows = DB::table('sdgs')->orderBy('id', 'asc')->get(['id', $hasSortOrder ? 'sort_order' : DB::raw('NULL AS sort_order')]);
        $i = 1;
        foreach ($rows as $row) {
            $order = $hasSortOrder && !is_null($row->sort_order) ? (int) $row->sort_order : $i;
            DB::table('sdgs')->where('id', $row->id)->update(['code' => 'SDG' . max(1, $order)]);
            $i++;
        }

        // Make NOT NULL and restore unique index
        try { DB::statement("ALTER TABLE `sdgs` MODIFY `code` VARCHAR(32) NOT NULL"); } catch (\Throwable $e) {}
        Schema::table('sdgs', function (Blueprint $table) {
            try { $table->unique('code', 'sdgs_code_unique'); } catch (\Throwable $e) {}
        });
    }
};
