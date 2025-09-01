<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('syllabus_course_infos', function (Blueprint $table) {
            $table->text('tla_strategies')->nullable()->after('course_description');
        });
    }

    public function down()
    {
        Schema::table('syllabus_course_infos', function (Blueprint $table) {
            $table->dropColumn('tla_strategies');
        });
    }
};
