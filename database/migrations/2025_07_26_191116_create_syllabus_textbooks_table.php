<?php

// File: database/migrations/xxxx_xx_xx_xxxxxx_create_syllabus_textbooks_table.php
// Description: Creates table for storing multiple textbook files per syllabus – Syllaverse

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('syllabus_textbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus_textbooks');
    }
};

