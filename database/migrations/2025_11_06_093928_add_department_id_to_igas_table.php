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
        Schema::table('igas', function (Blueprint $table) {
            if (!Schema::hasColumn('igas', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('id');
                $table->index('department_id', 'igas_department_id_index');
            }
        });

        // Add FK if departments table exists
        if (Schema::hasTable('departments')) {
            Schema::table('igas', function (Blueprint $table) {
                try {
                    $table->foreign('department_id', 'igas_department_id_fk')
                        ->references('id')->on('departments')
                        ->onDelete('set null');
                } catch (\Throwable $e) {
                    // Ignore if FK exists
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('igas', function (Blueprint $table) {
            // Drop FK and column if exist
            try { $table->dropForeign('igas_department_id_fk'); } catch (\Throwable $e) {}
            try { $table->dropIndex('igas_department_id_index'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('igas', 'department_id')) {
                $table->dropColumn('department_id');
            }
        });
    }
};
