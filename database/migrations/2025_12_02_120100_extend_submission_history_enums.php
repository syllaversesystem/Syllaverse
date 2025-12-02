<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add final_approved to both from_status and to_status enums in syllabus_submissions
        DB::statement("ALTER TABLE `syllabus_submissions` MODIFY COLUMN `from_status` ENUM('draft','pending_review','revision','approved','final_approval','final_approved') NOT NULL");
        DB::statement("ALTER TABLE `syllabus_submissions` MODIFY COLUMN `to_status` ENUM('draft','pending_review','revision','approved','final_approval','final_approved') NOT NULL");
    }

    public function down(): void
    {
        // Revert to original set (no final_approved)
        DB::statement("ALTER TABLE `syllabus_submissions` MODIFY COLUMN `from_status` ENUM('draft','pending_review','revision','approved','final_approval') NOT NULL");
        DB::statement("ALTER TABLE `syllabus_submissions` MODIFY COLUMN `to_status` ENUM('draft','pending_review','revision','approved','final_approval') NOT NULL");
    }
};
