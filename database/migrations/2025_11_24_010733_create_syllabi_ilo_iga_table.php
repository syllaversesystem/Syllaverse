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
        // Drop the old table if it exists
        Schema::dropIfExists('syllabi_ilo_iga');
        
        if (! Schema::hasTable('syllabus_ilo_iga')) {
            Schema::create('syllabus_ilo_iga', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('syllabus_id')->index();
                $table->string('ilo_text')->nullable();
                // store IGA values as JSON array (one per IGA column)
                $table->json('igas')->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();

                $table->foreign('syllabus_id')->references('id')->on('syllabi')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_ilo_iga');
    }
};
