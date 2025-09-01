<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('syllabus_course_infos')) return;

        Schema::table('syllabus_course_infos', function (Blueprint $table) {
            if (!Schema::hasColumn('syllabus_course_infos', 'criteria_lecture')) {
                $table->text('criteria_lecture')->nullable()->after('tla_strategies');
            }
            if (!Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory')) {
                $table->text('criteria_laboratory')->nullable()->after('criteria_lecture');
            }
            if (!Schema::hasColumn('syllabus_course_infos', 'criteria_lecture_title')) {
                $table->string('criteria_lecture_title')->nullable()->after('criteria_laboratory');
            }
            if (!Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory_title')) {
                $table->string('criteria_laboratory_title')->nullable()->after('criteria_lecture_title');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('syllabus_course_infos')) return;

        Schema::table('syllabus_course_infos', function (Blueprint $table) {
            if (Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory_title')) {
                $table->dropColumn('criteria_laboratory_title');
            }
            if (Schema::hasColumn('syllabus_course_infos', 'criteria_lecture_title')) {
                $table->dropColumn('criteria_lecture_title');
            }
            if (Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory')) {
                $table->dropColumn('criteria_laboratory');
            }
            if (Schema::hasColumn('syllabus_course_infos', 'criteria_lecture')) {
                $table->dropColumn('criteria_lecture');
            }
        });
    }
};
