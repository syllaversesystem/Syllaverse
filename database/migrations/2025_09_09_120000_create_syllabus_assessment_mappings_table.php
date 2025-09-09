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
        if (! Schema::hasTable('syllabus_assessment_mappings')) {
            Schema::create('syllabus_assessment_mappings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('syllabus_id');
                $table->string('name')->nullable();
                // use longText to match existing SQL dump which used longtext + JSON check
                $table->longText('week_marks')->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();

                $table->index(['syllabus_id', 'position'], 'syllabus_assessment_mappings_syllabus_id_position_index');
                $table->foreign('syllabus_id')->references('id')->on('syllabi')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('syllabus_assessment_mappings', function (Blueprint $table) {
            // drop foreign key if exists
            try {
                $table->dropForeign(['syllabus_id']);
            } catch (\Throwable $__e) {
                // noop
            }
        });

        Schema::dropIfExists('syllabus_assessment_mappings');
    }
};
