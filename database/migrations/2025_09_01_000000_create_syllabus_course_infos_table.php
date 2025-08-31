<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('syllabus_course_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');

            // Course-related overrides stored per-syllabus
            $table->string('course_title')->nullable();
            $table->string('course_code')->nullable();
            $table->string('course_category')->nullable();
            $table->text('course_prerequisites')->nullable();
            $table->string('semester')->nullable();
            $table->string('year_level')->nullable();
            $table->string('credit_hours_text')->nullable();

            // Instructor / reference fields
            $table->string('instructor_name')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('reference_cmo')->nullable();
            $table->string('instructor_designation')->nullable();
            $table->string('date_prepared')->nullable();
            $table->string('instructor_email')->nullable();
            $table->string('revision_no')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('revision_date')->nullable();

            // Course description and contact hours
            $table->text('course_description')->nullable();
            $table->integer('contact_hours_lec')->nullable();
            $table->integer('contact_hours_lab')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('syllabus_course_infos');
    }
};
