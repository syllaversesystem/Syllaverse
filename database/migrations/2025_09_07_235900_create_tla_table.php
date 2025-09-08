<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tla')) {
            Schema::create('tla', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('syllabus_id')->index();
                $table->string('ch')->nullable();
                $table->text('topic')->nullable();
                $table->string('wks')->nullable();
                $table->text('outcomes')->nullable();
                $table->string('ilo')->nullable();
                $table->string('so')->nullable();
                $table->string('delivery')->nullable();
                $table->integer('position')->default(0)->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tla')) {
            Schema::dropIfExists('tla');
        }
    }
};
