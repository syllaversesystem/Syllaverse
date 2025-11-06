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
        // Drop index if exists, then drop sort_order column
        try {
            $idx = DB::select("SHOW INDEX FROM `igas` WHERE Key_name = 'igas_sort_order_index'");
            if (!empty($idx)) {
                DB::statement("ALTER TABLE `igas` DROP INDEX `igas_sort_order_index`");
            }
        } catch (\Throwable $e) {}

        if (Schema::hasColumn('igas', 'sort_order')) {
            Schema::table('igas', function (Blueprint $table) {
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
        if (!Schema::hasColumn('igas', 'sort_order')) {
            Schema::table('igas', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('id');
            });
        }

        $ids = DB::table('igas')->orderBy('id', 'asc')->pluck('id')->all();
        $i = 1;
        foreach ($ids as $id) {
            DB::table('igas')->where('id', $id)->update(['sort_order' => $i]);
            $i++;
        }

        try { DB::statement("ALTER TABLE `igas` MODIFY `sort_order` INT UNSIGNED NOT NULL"); } catch (\Throwable $e) {}
        Schema::table('igas', function (Blueprint $table) {
            try { $table->index('sort_order', 'igas_sort_order_index'); } catch (\Throwable $e) {}
        });
    }
};
