<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('syllabus_course_infos')) {
            Schema::table('syllabus_course_infos', function (Blueprint $table) {
                if (!Schema::hasColumn('syllabus_course_infos', 'contact_hours_lec')) {
                    $table->unsignedTinyInteger('contact_hours_lec')->nullable()->after('contact_hours');
                }
                if (!Schema::hasColumn('syllabus_course_infos', 'contact_hours_lab')) {
                    $table->unsignedTinyInteger('contact_hours_lab')->nullable()->after('contact_hours_lec');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('syllabus_course_infos')) {
            Schema::table('syllabus_course_infos', function (Blueprint $table) {
                if (Schema::hasColumn('syllabus_course_infos', 'contact_hours_lab')) {
                    $table->dropColumn('contact_hours_lab');
                }
                if (Schema::hasColumn('syllabus_course_infos', 'contact_hours_lec')) {
                    $table->dropColumn('contact_hours_lec');
                }
            });
        }
    }
};
