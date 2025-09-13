<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        // Convert enum columns to VARCHAR to allow additional role/scope strings.
        // Uses raw statements to avoid requiring doctrine/dbal.
        DB::statement("ALTER TABLE `chair_requests` MODIFY `requested_role` VARCHAR(64) NOT NULL");
        DB::statement("ALTER TABLE `appointments` MODIFY `role` VARCHAR(64) NOT NULL");
        DB::statement("ALTER TABLE `appointments` MODIFY `scope_type` VARCHAR(64) NOT NULL");
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        // Revert to the original enums where possible. Note: this may fail if data contains values
        // not present in the original enum list.
    DB::statement("ALTER TABLE `chair_requests` MODIFY `requested_role` ENUM('DEPT_CHAIR') NOT NULL");
    DB::statement("ALTER TABLE `appointments` MODIFY `role` ENUM('DEPT_CHAIR','FACULTY') NOT NULL");
    DB::statement("ALTER TABLE `appointments` MODIFY `scope_type` ENUM('Department','Faculty') NOT NULL");
    }
};
