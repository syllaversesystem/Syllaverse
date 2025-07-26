<?php
// File: database/migrations/2025_07_27_180100_add_type_to_syllabus_textbooks_table.php
// Description: Adds a 'type' column to identify textbook classification (main/other) – Syllaverse

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('syllabus_textbooks', function (Blueprint $table) {
            $table->enum('type', ['main', 'other'])->default('main')->after('original_name');
        });
    }

    public function down(): void {
        Schema::table('syllabus_textbooks', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
