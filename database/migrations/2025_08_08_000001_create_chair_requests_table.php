<?php

// -------------------------------------------------------------------------------
// * File: database/migrations/2025_08_08_000001_create_chair_requests_table.php
// * Description: Create chair_requests table for Admin-submitted role/scope requests – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – supports Dept/Program Chair requests with approval metadata.
// -------------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // This creates a queue of Admin-submitted requests (Dept/Program Chair) for Superadmin review.
    // IMPORTANT: Conditional validation (e.g., program_id required when PROG_CHAIR) will be handled in form/request logic.
    public function up(): void
    {
        // ░░░ START: Up Migration ░░░
        if (!Schema::hasTable('chair_requests')) {
            Schema::create('chair_requests', function (Blueprint $table) {
                $table->id();

                // Who is asking for the role
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                // What role they want to hold
                $table->enum('requested_role', ['DEPT_CHAIR', 'PROG_CHAIR']);

                // Scope selection (dept is always required; program required only for PROG_CHAIR in app logic)
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                $table->foreignId('program_id')->nullable()->constrained()->cascadeOnDelete();

                // Review lifecycle
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('decided_at')->nullable();
                $table->text('notes')->nullable();

                $table->timestamps();

                // Helpful indexes for dashboards and filtering
                $table->index(['user_id', 'status']);
                $table->index(['requested_role', 'department_id', 'program_id']);
            });
        }
        // ░░░ END: Up Migration ░░░
    }

    // Drop the table cleanly on rollback.
    public function down(): void
    {
        // ░░░ START: Down Migration ░░░
        Schema::dropIfExists('chair_requests');
        // ░░░ END: Down Migration ░░░
    }
};
