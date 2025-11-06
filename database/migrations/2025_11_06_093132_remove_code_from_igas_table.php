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
        // Drop unique index on igas.code if exists, then drop the column
        try {
            $idx = DB::select("SHOW INDEX FROM `igas` WHERE Key_name = 'igas_code_unique'");
            if (!empty($idx)) {
                DB::statement("ALTER TABLE `igas` DROP INDEX `igas_code_unique`");
            }
        } catch (\Throwable $e) {}

        if (Schema::hasColumn('igas', 'code')) {
            Schema::table('igas', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add code column and backfill from sort_order or sequential
        if (!Schema::hasColumn('igas', 'code')) {
            Schema::table('igas', function (Blueprint $table) {
                $table->string('code', 32)->nullable()->after('id');
            });
        }

        $hasSortOrder = Schema::hasColumn('igas', 'sort_order');
        $rows = DB::table('igas')->orderBy('id', 'asc')->get(['id', $hasSortOrder ? 'sort_order' : DB::raw('NULL AS sort_order')]);
        $i = 1;
        foreach ($rows as $row) {
            $order = $hasSortOrder && !is_null($row->sort_order) ? (int) $row->sort_order : $i;
            DB::table('igas')->where('id', $row->id)->update(['code' => 'IGA' . max(1, $order)]);
            $i++;
        }

        try { DB::statement("ALTER TABLE `igas` MODIFY `code` VARCHAR(32) NOT NULL"); } catch (\Throwable $e) {}
        Schema::table('igas', function (Blueprint $table) {
            try { $table->unique('code', 'igas_code_unique'); } catch (\Throwable $e) {}
        });
    }
};
