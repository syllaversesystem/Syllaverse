<?php

// -----------------------------------------------------------------------------
// File: database/migrations/2025_07_29_000002_create_tla_so_table.php
// Description: Pivot table linking TLA rows to Syllabus SOs (many-to-many) – Syllaverse
// -----------------------------------------------------------------------------
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tla_so', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tla_id')->constrained('tla')->onDelete('cascade');
            $table->foreignId('syllabus_so_id')->constrained('syllabus_sos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tla_so');
    }
};
