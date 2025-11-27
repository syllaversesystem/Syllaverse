<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faculty_syllabus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');
            $table->enum('role', ['owner', 'collaborator', 'viewer'])->default('collaborator');
            $table->boolean('can_edit')->default(false);
            $table->timestamps();

            // Ensure unique faculty-syllabus combination
            $table->unique(['faculty_id', 'syllabus_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_syllabus');
    }
};
