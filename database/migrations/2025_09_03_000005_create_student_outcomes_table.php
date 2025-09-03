<?php

// -----------------------------------------------------------------------------
// File: database/migrations/2025_09_03_000005_create_student_outcomes_table.php
// Description: Creates the master `student_outcomes` table used by admin master data
// -----------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('student_outcomes')) {
            Schema::create('student_outcomes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable()->unique(false);
                $table->text('description');
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_outcomes');
    }
};
