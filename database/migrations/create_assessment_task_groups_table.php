<?php

// -----------------------------------------------------------------------------
// * File: database/migrations/*_create_assessment_task_groups_table.php
// * Description: Creates table for Assessment Task Groups (e.g., LEC, LAB) used
// *              to organize assessment tasks into Lecture/Laboratory subtabs.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Initial creation – assessment_task_groups with unique code/slug,
//              sort_order for drag reordering, and is_active flag.
// -----------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This creates the `assessment_task_groups` table for grouping tasks
     * like LEC (Lecture) and LAB (Laboratory). The UI will use these as subtabs.
     */
    // ░░░ START: up() – Create table ░░░
    public function up(): void
    {
        Schema::create('assessment_task_groups', function (Blueprint $table) {
            // START: Primary Key
            $table->id();
            // END: Primary Key

            // START: Group Identity
            // Short code shown in UI tabs, e.g., "LEC" or "LAB"
            $table->string('code', 16)->unique();

            // Human-readable label, e.g., "Lecture" or "Laboratory"
            $table->string('title', 100);

            // URL/lookup friendly identifier, e.g., "lecture", "laboratory"
            $table->string('slug', 64)->unique();
            // END: Group Identity

            // START: Ordering & Status
            // Used by drag-to-reorder; default 0 so we can sort ascending.
            $table->integer('sort_order')->default(0);

            // Soft enable/disable in UI without deleting.
            $table->boolean('is_active')->default(true);
            // END: Ordering & Status

            // START: Timestamps
            $table->timestamps();
            // END: Timestamps
        });
    }
    // ░░░ END: up() – Create table ░░░

    /**
     * This drops the `assessment_task_groups` table (rollback support).
     */
    // ░░░ START: down() – Drop table ░░░
    public function down(): void
    {
        Schema::dropIfExists('assessment_task_groups');
    }
    // ░░░ END: down() – Drop table ░░░
};
