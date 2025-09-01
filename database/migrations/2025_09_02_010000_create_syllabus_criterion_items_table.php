<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('syllabus_criterion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_criterion_id')->constrained('syllabus_criteria')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->string('percent')->nullable();
            $table->integer('position')->default(0);
            $table->string('section')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('syllabus_criterion_items');
    }
};
