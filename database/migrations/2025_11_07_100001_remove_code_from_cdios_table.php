<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cdios', 'code')) {
            // Drop unique index on code if present (guarded)
            try {
                $indexes = DB::select("SHOW INDEX FROM `cdios` WHERE Column_name = 'code'");
                foreach ($indexes as $idx) {
                    if (!empty($idx->Key_name)) {
                        Schema::table('cdios', function (Blueprint $table) use ($idx) {
                            try { $table->dropIndex($idx->Key_name); } catch (\Throwable $e) { /* no-op */ }
                        });
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }

            Schema::table('cdios', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('cdios', 'code')) {
            Schema::table('cdios', function (Blueprint $table) {
                $table->string('code')->nullable();
            });
            // Backfill a simple sequential code (CDIO1, CDIO2, ...)
            try {
                $rows = DB::table('cdios')->orderBy('id')->get(['id']);
                $n = 1;
                foreach ($rows as $row) {
                    DB::table('cdios')->where('id', $row->id)->update(['code' => 'CDIO' . $n]);
                    $n++;
                }
                // Make code non-null and unique after backfill
                Schema::table('cdios', function (Blueprint $table) {
                    $table->string('code')->nullable(false)->change();
                    $table->unique('code', 'cdios_code_unique');
                });
            } catch (\Throwable $e) {
                // Leave as nullable without unique if backfill fails
            }
        }
    }
};
