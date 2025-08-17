<?php

// -----------------------------------------------------------------------------
// * File: database/migrations/*_create_assessment_tasks_table.php
// * Description: Creates table for Assessment Tasks (e.g., ME, FE, QCT, ARR, PR,
// *              LE, LEX) and links them to a parent Assessment Task Group.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Initial creation – assessment_tasks with FK to groups, composite
//              unique on (group_id, code), reorder indexes, is_active flag.
// -----------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This creates the `assessment_tasks` table that stores the individual tasks
     * (like Midterm Exam, Final Examination). Each task belongs to a group
     * (LEC or LAB), and can be reordered within that group.
     */
    // ░░░ START: up() – Create table ░░░
    public function up(): void
    {
        Schema::create('assessment_tasks', function (Blueprint $table) {
            // START: Primary Key
            $table->id();
            // END: Primary Key

            // START: Relationship to Group (LEC/LAB)
            // Foreign key to assessment_task_groups; cascade so child rows are removed with the group.
            $table->foreignId('group_id')
                ->constrained('assessment_task_groups')
                ->cascadeOnDelete();
            // END: Relationship to Group (LEC/LAB)

            // START: Task Identity
            // Short per-group code like "ME", "FE", "QCT", "ARR", "PR", "LE", "LEX"
            $table->string('code', 16);

            // Human-readable title like "Midterm Exam"
            $table->string('title', 150);

            // Optional description for tooltips/notes
            $table->text('description')->nullable();
            // END: Task Identity

            // START: Ordering & Status
            // Used for drag-to-reorder within the group
            $table->integer('sort_order')->default(0);

            // Soft enable/disable in UI without deleting
            $table->boolean('is_active')->default(true);
            // END: Ordering & Status

            // START: Timestamps
            $table->timestamps();
            // END: Timestamps

            // START: Indexes & Constraints
            // Ensure no duplicate codes within the same group (e.g., two "ME" in LEC)
            $table->unique(['group_id', 'code']);

            // Speed up list/reorder queries per group
            $table->index(['group_id', 'sort_order']);
            // END: Indexes & Constraints
        });
    }
    // ░░░ END: up() – Create table ░░░

    /**
     * This drops the `assessment_tasks` table (rollback support).
     */
    // ░░░ START: down() – Drop table ░░░
    public function down(): void
    {
        Schema::dropIfExists('assessment_tasks');
    }
    // ░░░ END: down() – Drop table ░░░
};
