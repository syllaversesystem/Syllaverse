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
        Schema::create('syllabus_ilo_iga', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('syllabus_id')->index();
            $table->text('ilo_text')->nullable();
            $table->json('igas')->nullable();
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
        Schema::dropIfExists('syllabus_ilo_iga');
    }
};
