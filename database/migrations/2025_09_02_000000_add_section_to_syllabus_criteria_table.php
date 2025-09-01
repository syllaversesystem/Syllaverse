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
        Schema::table('syllabus_criteria', function (Blueprint $table) {
            if (!Schema::hasColumn('syllabus_criteria', 'section')) {
                $table->string('section')->nullable()->after('heading');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('syllabus_criteria', function (Blueprint $table) {
            if (Schema::hasColumn('syllabus_criteria', 'section')) {
                $table->dropColumn('section');
            }
        });
    }
};
