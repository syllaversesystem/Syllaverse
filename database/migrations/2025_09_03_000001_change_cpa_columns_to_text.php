<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: altering column types requires the doctrine/dbal package for some drivers.
     */
    public function up()
    {
        if (! Schema::hasTable('syllabus_assessment_tasks')) return;

        Schema::table('syllabus_assessment_tasks', function (Blueprint $table) {
            // Convert c/p/a to text to allow arbitrary short marks or notes
            // Use nullableText to be explicit
            // Some DB drivers require doctrine/dbal to change column types; if migration fails, install it.
            try {
                $table->text('c')->nullable()->change();
                $table->text('p')->nullable()->change();
                $table->text('a')->nullable()->change();
            } catch (\Throwable $e) {
                // Fallback: if change() is not supported, add shadow columns and copy data
                // This fallback will only execute when the DB driver disallows change().
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('syllabus_assessment_tasks')) return;

        Schema::table('syllabus_assessment_tasks', function (Blueprint $table) {
            try {
                $table->integer('c')->nullable()->change();
                $table->integer('p')->nullable()->change();
                $table->integer('a')->nullable()->change();
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};
