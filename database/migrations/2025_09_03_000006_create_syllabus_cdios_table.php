<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('syllabus_cdios')) {
            Schema::create('syllabus_cdios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('syllabus_cdios');
    }
};
