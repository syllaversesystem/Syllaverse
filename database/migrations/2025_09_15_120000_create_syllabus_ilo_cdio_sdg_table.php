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
        Schema::create('syllabus_ilo_cdio_sdg', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('syllabus_id')->index();
            $table->text('ilo_text')->nullable();
            $table->json('cdios')->nullable();
            $table->json('sdgs')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->foreign('syllabus_id')->references('id')->on('syllabi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('syllabus_ilo_cdio_sdg');
    }
};
