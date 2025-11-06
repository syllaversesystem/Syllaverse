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
        // Drop index if exists, then drop column `sort_order`
        try {
            $idx = DB::select("SHOW INDEX FROM `sdgs` WHERE Key_name = 'sdgs_sort_order_index'");
            if (!empty($idx)) {
                DB::statement("ALTER TABLE `sdgs` DROP INDEX `sdgs_sort_order_index`");
            }
        } catch (\Throwable $e) {
            // ignore
        }

        if (Schema::hasColumn('sdgs', 'sort_order')) {
            Schema::table('sdgs', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add sort_order and backfill sequential by id; then NOT NULL + index
        if (!Schema::hasColumn('sdgs', 'sort_order')) {
            Schema::table('sdgs', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('id');
            });
        }

        $rows = DB::table('sdgs')->orderBy('id', 'asc')->pluck('id')->all();
        $i = 1;
        foreach ($rows as $id) {
            DB::table('sdgs')->where('id', $id)->update(['sort_order' => $i]);
            $i++;
        }

        try { DB::statement("ALTER TABLE `sdgs` MODIFY `sort_order` INT UNSIGNED NOT NULL"); } catch (\Throwable $e) {}
        Schema::table('sdgs', function (Blueprint $table) {
            try { $table->index('sort_order', 'sdgs_sort_order_index'); } catch (\Throwable $e) {}
        });
    }
};
