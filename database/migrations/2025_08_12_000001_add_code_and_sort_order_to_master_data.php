<?php

// -------------------------------------------------------------------------------
// * File: database/migrations/2025_08_12_000001_add_code_and_sort_order_to_master_data.php
// * Description: Add `code` (e.g., SDG1/IGA3/CDIO2) and `sort_order` to sdgs/igas/cdios; backfill & index – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-12] Initial creation – add columns, backfill sequential codes by current ID order,
//              add unique(code) + index(sort_order), and make columns NOT NULL.
// -------------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * This adds `code` and `sort_order` to sdgs/igas/cdios, fills them with sequential values
     * (e.g., SDG1, SDG2, …) ordered by current IDs, then enforces NOT NULL + indexes.
     */
    public function up(): void
    {
        // START: Add nullable columns first (so we can backfill)
        Schema::table('sdgs', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->after('id');
            $table->unsignedInteger('sort_order')->nullable()->after('code');
        });
        Schema::table('igas', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->after('id');
            $table->unsignedInteger('sort_order')->nullable()->after('code');
        });
        Schema::table('cdios', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->after('id');
            $table->unsignedInteger('sort_order')->nullable()->after('code');
        });
        // END: Add columns

        // START: Backfill (use current ID ascending as initial order)
        $this->backfillCodesAndOrder('sdgs',  'SDG');
        $this->backfillCodesAndOrder('igas',  'IGA');
        $this->backfillCodesAndOrder('cdios', 'CDIO');
        // END: Backfill

        // START: Add indexes after data is valid; then enforce NOT NULL (MySQL-safe via raw SQL)
        Schema::table('sdgs', function (Blueprint $table) {
            $table->unique('code', 'sdgs_code_unique');
            $table->index('sort_order', 'sdgs_sort_order_index');
        });
        Schema::table('igas', function (Blueprint $table) {
            $table->unique('code', 'igas_code_unique');
            $table->index('sort_order', 'igas_sort_order_index');
        });
        Schema::table('cdios', function (Blueprint $table) {
            $table->unique('code', 'cdios_code_unique');
            $table->index('sort_order', 'cdios_sort_order_index');
        });

        // Make columns NOT NULL without requiring doctrine/dbal
        $this->makeNotNull('sdgs');
        $this->makeNotNull('igas');
        $this->makeNotNull('cdios');
        // END: Indexes + NOT NULL
    }

    /**
     * This removes the columns and indexes, reverting to the previous schema.
     */
    public function down(): void
    {
        // Drop indexes first (safe even if missing)
        Schema::table('sdgs', function (Blueprint $table) {
            try { $table->dropUnique('sdgs_code_unique'); } catch (\Throwable $e) {}
            try { $table->dropIndex('sdgs_sort_order_index'); } catch (\Throwable $e) {}
        });
        Schema::table('igas', function (Blueprint $table) {
            try { $table->dropUnique('igas_code_unique'); } catch (\Throwable $e) {}
            try { $table->dropIndex('igas_sort_order_index'); } catch (\Throwable $e) {}
        });
        Schema::table('cdios', function (Blueprint $table) {
            try { $table->dropUnique('cdios_code_unique'); } catch (\Throwable $e) {}
            try { $table->dropIndex('cdios_sort_order_index'); } catch (\Throwable $e) {}
        });

        // Drop columns
        Schema::table('sdgs', function (Blueprint $table) {
            if (Schema::hasColumn('sdgs', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('sdgs', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
        Schema::table('igas', function (Blueprint $table) {
            if (Schema::hasColumn('igas', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('igas', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
        Schema::table('cdios', function (Blueprint $table) {
            if (Schema::hasColumn('cdios', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('cdios', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }

    // ░░░ START: Helpers ░░░

    /**
     * Backfill code/sort_order sequentially by current ID order.
     * @param string $table  Table name (sdgs|igas|cdios)
     * @param string $prefix Code prefix (SDG|IGA|CDIO)
     */
    protected function backfillCodesAndOrder(string $table, string $prefix): void
    {
        // Fetch IDs in a deterministic order (existing ID asc)
        $ids = DB::table($table)->orderBy('id', 'asc')->pluck('id')->all();
        $i = 1;
        foreach ($ids as $id) {
            DB::table($table)->where('id', $id)->update([
                'sort_order' => $i,
                'code'       => $prefix . $i,
            ]);
            $i++;
        }
    }

    /**
     * Make the new columns NOT NULL using raw SQL (avoids doctrine/dbal).
     * Adjusts to MySQL syntax; if using other drivers, tweak accordingly.
     */
    protected function makeNotNull(string $table): void
    {
        // Detect the connection driver if you want branching; here we assume MySQL/MariaDB
        DB::statement("ALTER TABLE `{$table}` MODIFY `code` VARCHAR(32) NOT NULL");
        DB::statement("ALTER TABLE `{$table}` MODIFY `sort_order` INT UNSIGNED NOT NULL");
    }

    // ░░░ END: Helpers ░░░
};
