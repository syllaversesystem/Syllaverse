<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('courses', function (Blueprint $table) {
        $table->dropColumn(['units_lec', 'units_lab']);
    });
}

public function down()
{
    Schema::table('courses', function (Blueprint $table) {
        $table->integer('units_lec')->default(0);
        $table->integer('units_lab')->default(0);
    });
}

};
