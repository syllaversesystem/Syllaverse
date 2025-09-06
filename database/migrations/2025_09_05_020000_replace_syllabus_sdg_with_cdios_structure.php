<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration drops the legacy `syllabus_sdg` table (if exists)
     * and (re)creates a normalized `syllabus_sdgs` table using the
     * same shape as the master `cdios` table but scoped to a syllabus.
     *
     * Columns: id, syllabus_id (FK), code, sort_order, title, description, timestamps
     */
    public function up(): void
    {
        // drop legacy singular table if present
        if (Schema::hasTable('syllabus_sdg')) {
            Schema::dropIfExists('syllabus_sdg');
        }

        // drop existing plural table to ensure a clean recreation
        Schema::dropIfExists('syllabus_sdgs');

        Schema::create('syllabus_sdgs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->cascadeOnDelete();
            $table->string('code', 32);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('title');
            $table->text('description');
            $table->timestamps();

            $table->unique(['syllabus_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Recreate the old `syllabus_sdg` table shape so the migration is reversible.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_sdgs');

        Schema::create('syllabus_sdg', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->cascadeOnDelete();
            $table->foreignId('sdg_id')->constrained('sdgs')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->integer('position')->nullable();
            $table->string('code')->nullable();
            $table->timestamps();

            $table->unique(['syllabus_id', 'sdg_id']);
        });
    }
};
