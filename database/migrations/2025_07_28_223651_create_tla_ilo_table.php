<?php

// -----------------------------------------------------------------------------
// File: database/migrations/2025_07_29_000001_create_tla_ilo_table.php
// Description: Pivot table linking TLA rows to Syllabus ILOs (many-to-many) – Syllaverse
// -----------------------------------------------------------------------------
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tla_ilo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tla_id')->constrained('tla')->onDelete('cascade');
            $table->foreignId('syllabus_ilo_id')->constrained('syllabus_ilos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tla_ilo');
    }
};
