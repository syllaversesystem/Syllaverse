<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('syllabus_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');
            $table->string('key');
            $table->string('heading')->nullable();
            // store the list values as JSON array for flexibility
            $table->json('value')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->unique(['syllabus_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('syllabus_criteria');
    }
};
