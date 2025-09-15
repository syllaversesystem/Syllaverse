<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('syllabus_ilo_so_cpa')) {
            Schema::create('syllabus_ilo_so_cpa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('syllabus_id')->index();
                $table->string('ilo_text')->nullable();
                // store SO values as JSON array (one per SO column)
                $table->json('sos')->nullable();
                $table->text('c')->nullable();
                $table->text('p')->nullable();
                $table->text('a')->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();

                $table->foreign('syllabus_id')->references('id')->on('syllabi')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('syllabus_ilo_so_cpa');
    }
};
