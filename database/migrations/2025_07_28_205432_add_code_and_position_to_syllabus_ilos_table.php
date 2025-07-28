<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('syllabus_ilos', function (Blueprint $table) {
        $table->string('code')->after('syllabus_id');
        $table->integer('position')->default(0)->after('description');
    });
}

public function down()
{
    Schema::table('syllabus_ilos', function (Blueprint $table) {
        $table->dropColumn(['code', 'position']);
    });
}

};
