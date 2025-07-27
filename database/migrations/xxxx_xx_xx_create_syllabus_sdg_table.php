<?php

// File: database/migrations/2025_07_28_000001_create_syllabus_sdg_table.php
// Description: Pivot table for mapping Sustainable Development Goals (SDG) to Syllabus with editable fields – Syllaverse

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('syllabus_sdg', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('syllabus_id')->constrained()->onDelete('cascade');
            $table->foreignId('sdg_id')->constrained()->onDelete('cascade');

            // Editable fields per mapping
            $table->string('title'); // Overridable SDG title (optional: make nullable if not always filled)
            $table->text('description');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus_sdg');
    }
};
