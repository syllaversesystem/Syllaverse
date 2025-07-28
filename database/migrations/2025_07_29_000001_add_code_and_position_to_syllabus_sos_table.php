<?php

// -------------------------------------------------------------------------------
// * File: database/migrations/2025_07_29_000001_add_code_and_position_to_syllabus_sos_table.php
// * Description: Adds `code` and `position` columns to syllabus_sos for SO cloning – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Initial creation – added code and position fields to support cloned SO attributes.
// -------------------------------------------------------------------------------


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('syllabus_sos', function (Blueprint $table) {
            $table->string('code')->after('syllabus_id');
            $table->integer('position')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabus_sos', function (Blueprint $table) {
            $table->dropColumn(['code', 'position']);
        });
    }
};
