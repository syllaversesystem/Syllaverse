<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop tla and related pivot tables if they exist.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tla_ilo');
        Schema::dropIfExists('tla_so');
        Schema::dropIfExists('tla');
    }

    /**
     * Reverse the migrations.
     * Recreate a minimal tla schema to allow rollbacks (non-destructive payload columns nullable).
     * NOTE: This recreates basic columns only; indexes/foreign keys from the original dump are not re-added.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('tla', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('syllabus_id')->nullable();
            $table->string('ch')->nullable();
            $table->text('topic')->nullable();
            $table->string('wks')->nullable();
            $table->text('outcomes')->nullable();
            $table->string('delivery')->nullable();
            $table->timestamps();
        });

        Schema::create('tla_ilo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tla_id')->nullable();
            $table->unsignedBigInteger('ilo_id')->nullable();
            $table->timestamps();
        });

        Schema::create('tla_so', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tla_id')->nullable();
            $table->unsignedBigInteger('so_id')->nullable();
            $table->timestamps();
        });
    }
};
