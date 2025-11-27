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
        Schema::table('syllabus_ilo_cdio_sdg', function (Blueprint $table) {
            $table->json('cdio_labels')->nullable()->after('ilo_text');
            $table->json('sdg_labels')->nullable()->after('cdio_labels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabus_ilo_cdio_sdg', function (Blueprint $table) {
            $table->dropColumn(['cdio_labels', 'sdg_labels']);
        });
    }
};
