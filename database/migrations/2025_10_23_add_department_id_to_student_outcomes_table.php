<?php

// -----------------------------------------------------------------------------
// File: database/migrations/2025_10_23_add_department_id_to_student_outcomes_table.php
// Description: Adds department_id column to student_outcomes table for department-specific SOs
// -----------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('student_outcomes')) {
            Schema::table('student_outcomes', function (Blueprint $table) {
                if (!Schema::hasColumn('student_outcomes', 'department_id')) {
                    $table->unsignedBigInteger('department_id')->nullable()->after('id');
                    $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
                    
                    // Update unique constraint to include department_id
                    $table->dropUnique(['code']);
                    $table->unique(['code', 'department_id']);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_outcomes')) {
            Schema::table('student_outcomes', function (Blueprint $table) {
                if (Schema::hasColumn('student_outcomes', 'department_id')) {
                    $table->dropForeign(['department_id']);
                    $table->dropUnique(['code', 'department_id']);
                    $table->dropColumn('department_id');
                    
                    // Restore original unique constraint
                    $table->unique('code');
                }
            });
        }
    }
};