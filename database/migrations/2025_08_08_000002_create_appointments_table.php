<?php

// -------------------------------------------------------------------------------
// * File: database/migrations/2025_08_08_000002_create_appointments_table.php
// * Description: Create appointments table (grants Dept/Program Chair power with scope & dates) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – supports DEPT_CHAIR/PROG_CHAIR, scoped to Department/Program with start/end.
// -------------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // This table represents ACTIVE authority. Chair requests are reviewed elsewhere; approval creates rows here.
    public function up(): void
    {
        // ░░░ START: Up Migration ░░░
        if (!Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();

                // Who holds the authority
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                // What authority they hold
                $table->enum('role', ['DEPT_CHAIR', 'PROG_CHAIR']);

                // Where that authority applies (polymorphic-ish: enum + id; no FK due to dual targets)
                $table->enum('scope_type', ['Department', 'Program']);
                $table->unsignedBigInteger('scope_id');

                // Lifecycle + assignment metadata
                $table->enum('status', ['active', 'ended'])->default('active');
                $table->timestamp('start_at')->useCurrent();
                $table->timestamp('end_at')->nullable();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamps();

                // Helpful indexes
                $table->index(['user_id', 'role', 'status']);
                $table->index(['scope_type', 'scope_id', 'role', 'status']);
            });
        }
        // ░░░ END: Up Migration ░░░
    }

    // Remove the authority table if rolled back.
    public function down(): void
    {
        // ░░░ START: Down Migration ░░░
        Schema::dropIfExists('appointments');
        // ░░░ END: Down Migration ░░░
    }
};
