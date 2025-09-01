<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop normalized criteria tables if they exist
        Schema::dropIfExists('syllabus_criterion_items');
        Schema::dropIfExists('syllabus_criteria');
    }

    public function down()
    {
        // Recreate a minimal schema to allow rollback if needed
        if (! Schema::hasTable('syllabus_criteria')) {
            Schema::create('syllabus_criteria', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('syllabus_id')->nullable();
                $table->string('key')->nullable();
                $table->string('heading')->nullable();
                $table->json('value')->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('syllabus_criterion_items')) {
            Schema::create('syllabus_criterion_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('syllabus_criterion_id')->nullable();
                $table->string('description')->nullable();
                $table->string('percent')->nullable();
                $table->integer('position')->default(0);
                $table->string('section')->nullable();
                $table->timestamps();
            });
        }
    }
};
