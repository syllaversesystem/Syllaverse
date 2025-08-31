<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Note: This migration uses the `change()` method which requires the
     * doctrine/dbal package. Run `composer require doctrine/dbal` before
     * running `php artisan migrate` if your environment doesn't already have it.
     */
    public function up()
    {
        if (Schema::hasTable('syllabus_course_infos')) {
            Schema::table('syllabus_course_infos', function (Blueprint $table) {
                if (Schema::hasColumn('syllabus_course_infos', 'contact_hours_lec')) {
                    $table->text('contact_hours_lec')->nullable()->change();
                }
                if (Schema::hasColumn('syllabus_course_infos', 'contact_hours_lab')) {
                    $table->text('contact_hours_lab')->nullable()->change();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('syllabus_course_infos')) {
            Schema::table('syllabus_course_infos', function (Blueprint $table) {
                // Attempt to revert to unsignedTinyInteger. This also requires doctrine/dbal.
                if (Schema::hasColumn('syllabus_course_infos', 'contact_hours_lec')) {
                    $table->unsignedTinyInteger('contact_hours_lec')->nullable()->change();
                }
                if (Schema::hasColumn('syllabus_course_infos', 'contact_hours_lab')) {
                    $table->unsignedTinyInteger('contact_hours_lab')->nullable()->change();
                }
            });
        }
    }
};
