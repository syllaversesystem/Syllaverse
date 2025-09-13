<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chair_requests', function (Blueprint $table) {
            // Drop existing foreign key + column constraint, then make nullable
            $table->dropForeign(['department_id']);
            $table->unsignedBigInteger('department_id')->nullable()->change();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('chair_requests', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }
};
