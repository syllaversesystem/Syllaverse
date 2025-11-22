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
        // Drop the extra tables that were created during restructure attempt
        Schema::dropIfExists('syllabus_ilo_cpa');
        Schema::dropIfExists('syllabus_ilo_so_mappings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate tables if needed to rollback
        Schema::create('syllabus_ilo_so_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ilo_so_cpa_id');
            $table->string('so_code', 50);
            $table->text('so_value')->nullable();
            $table->integer('so_position')->default(0);
            $table->timestamps();

            $table->foreign('ilo_so_cpa_id')->references('id')->on('syllabus_ilo_so_cpa')->onDelete('cascade');
            $table->unique(['ilo_so_cpa_id', 'so_code']);
        });

        Schema::create('syllabus_ilo_cpa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ilo_so_cpa_id');
            $table->text('cognitive')->nullable();
            $table->text('psychomotor')->nullable();
            $table->text('affective')->nullable();
            $table->timestamps();

            $table->foreign('ilo_so_cpa_id')->references('id')->on('syllabus_ilo_so_cpa')->onDelete('cascade');
        });
    }
};
