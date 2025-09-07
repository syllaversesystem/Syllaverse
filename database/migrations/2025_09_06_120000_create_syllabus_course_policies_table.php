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
        if (!Schema::hasTable('syllabus_course_policies')) {
            Schema::create('syllabus_course_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');
                $table->string('section')->index();
                $table->text('content')->nullable();
                $table->integer('position')->nullable()->default(0);
                $table->timestamps();
            });
        } else {
            // table exists already (older deployment) - ensure required columns exist
            Schema::table('syllabus_course_policies', function (Blueprint $table) {
                if (!Schema::hasColumn('syllabus_course_policies', 'section')) {
                    $table->string('section')->after('syllabus_id')->index();
                }
                if (!Schema::hasColumn('syllabus_course_policies', 'content')) {
                    $table->text('content')->nullable()->after('section');
                }
                if (!Schema::hasColumn('syllabus_course_policies', 'position')) {
                    $table->integer('position')->nullable()->default(0)->after('content');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_course_policies');
    }
};
