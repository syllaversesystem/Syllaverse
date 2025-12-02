<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Expand enum to include final_approved
        DB::statement("ALTER TABLE `syllabi` MODIFY COLUMN `submission_status` ENUM('draft','pending_review','revision','approved','final_approval','final_approved') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to prior enum set (without final_approved)
        DB::statement("ALTER TABLE `syllabi` MODIFY COLUMN `submission_status` ENUM('draft','pending_review','revision','approved','final_approval') NOT NULL DEFAULT 'draft'");
    }
};
