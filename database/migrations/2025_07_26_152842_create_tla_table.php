<?php

// File: database/migrations/xxxx_xx_xx_xxxxxx_create_tla_table.php
// Description: Migration for TLA rows per syllabus – Syllaverse

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');

            $table->string('ch')->nullable();
            $table->text('topic')->nullable();
            $table->string('wks')->nullable();
            $table->text('outcomes')->nullable();
            $table->string('ilo')->nullable();
            $table->string('so')->nullable();
            $table->string('delivery')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tla');
    }
};
