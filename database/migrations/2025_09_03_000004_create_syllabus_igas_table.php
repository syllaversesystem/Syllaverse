<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syllabus_igas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained()->onDelete('cascade');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus_igas');
    }
};
