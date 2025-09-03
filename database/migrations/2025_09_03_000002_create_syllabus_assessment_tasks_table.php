<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syllabus_assessment_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->cascadeOnDelete();
            $table->string('section')->nullable(); // e.g., LEC, LAB or 'LEC — LECTURE'
            $table->string('code', 32)->nullable();
            $table->text('task')->nullable();
            $table->string('ird', 16)->nullable();
            $table->decimal('percent', 8, 2)->nullable();
            $table->json('ilo_flags')->nullable(); // array of flags or values per ILO column
            $table->integer('c')->nullable();
            $table->integer('p')->nullable();
            $table->integer('a')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->index(['syllabus_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus_assessment_tasks');
    }
};
