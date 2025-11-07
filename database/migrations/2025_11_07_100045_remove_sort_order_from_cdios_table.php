<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cdios', 'sort_order')) {
            // Drop index on sort_order if present
            try {
                $indexes = DB::select("SHOW INDEX FROM `cdios` WHERE Column_name = 'sort_order'");
                foreach ($indexes as $idx) {
                    if (!empty($idx->Key_name)) {
                        Schema::table('cdios', function (Blueprint $table) use ($idx) {
                            try { $table->dropIndex($idx->Key_name); } catch (\Throwable $e) { /* ignore */ }
                        });
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }

            Schema::table('cdios', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('cdios', 'sort_order')) {
            Schema::table('cdios', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable();
                $table->index('sort_order', 'cdios_sort_order_index');
            });
            // Backfill sequential sort_order by id
            try {
                $rows = DB::table('cdios')->orderBy('id')->get(['id']);
                $n = 1;
                foreach ($rows as $row) {
                    DB::table('cdios')->where('id', $row->id)->update(['sort_order' => $n]);
                    $n++;
                }
                Schema::table('cdios', function (Blueprint $table) {
                    $table->unsignedInteger('sort_order')->nullable(false)->change();
                });
            } catch (\Throwable $e) {
                // leave nullable if backfill fails
            }
        }
    }
};
