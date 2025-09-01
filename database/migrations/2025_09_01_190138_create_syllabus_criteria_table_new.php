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
        // Check if table already exists
        if (Schema::hasTable('syllabus_criteria')) {
            return;
        }

        Schema::create('syllabus_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');
            $table->string('key'); // e.g., 'lecture', 'laboratory', 'major_requirements'
            $table->string('heading')->nullable(); // e.g., 'Lecture', 'Laboratory', 'Major Requirements'
            $table->string('section')->nullable(); // e.g., 'Assessment', 'Requirements'
            $table->json('value')->nullable(); // store the list values as JSON array for flexibility
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->unique(['syllabus_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_criteria');
    }
};
