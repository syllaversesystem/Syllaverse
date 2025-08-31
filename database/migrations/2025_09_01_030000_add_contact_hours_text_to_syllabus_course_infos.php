<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('syllabus_course_infos') && !Schema::hasColumn('syllabus_course_infos', 'contact_hours')) {
            Schema::table('syllabus_course_infos', function (Blueprint $table) {
                $table->text('contact_hours')->nullable()->after('course_description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('syllabus_course_infos') && Schema::hasColumn('syllabus_course_infos', 'contact_hours')) {
            Schema::table('syllabus_course_infos', function (Blueprint $table) {
                $table->dropColumn('contact_hours');
            });
        }
    }
};
