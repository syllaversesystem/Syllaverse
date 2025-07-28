<?php

// //-------------------------------------------------------------------------------
// * File: database/migrations/2025_07_29_000001_add_position_to_student_outcomes_table.php
// * Description: Adds 'position' column to support draggable ordering of SOs – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Migration created to add position field to student_outcomes table.
// -------------------------------------------------------------------------------


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add 'position' column to student_outcomes table for reordering support.
     */
    public function up(): void
    {
        Schema::table('student_outcomes', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('description');
        });
    }

    /**
     * Rollback the 'position' column.
     */
    public function down(): void
    {
        Schema::table('student_outcomes', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
