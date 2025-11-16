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
        // Check if columns already exist before adding
        if (!Schema::hasColumn('syllabus_assessment_tasks', 'section_number')) {
            Schema::table('syllabus_assessment_tasks', function (Blueprint $table) {
                $table->integer('section_number')->nullable()->after('syllabus_id');
            });
        }
        
        if (!Schema::hasColumn('syllabus_assessment_tasks', 'row_type')) {
            Schema::table('syllabus_assessment_tasks', function (Blueprint $table) {
                $table->enum('row_type', ['main', 'sub'])->default('sub')->after('section_number');
            });
        }
        
        if (!Schema::hasColumn('syllabus_assessment_tasks', 'section_label')) {
            Schema::table('syllabus_assessment_tasks', function (Blueprint $table) {
                $table->string('section_label')->nullable();
            });
        }
        
        // Add index for better query performance with custom shorter name
        Schema::table('syllabus_assessment_tasks', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEX FROM syllabus_assessment_tasks WHERE Key_name = 'sat_section_row_pos_idx'");
            if (empty($indexes)) {
                $table->index(['syllabus_id', 'section_number', 'row_type', 'position'], 'sat_section_row_pos_idx');
            }
        });
        
        // Migrate existing data: if section is not empty, treat as main row
        DB::statement("UPDATE syllabus_assessment_tasks SET section_number = 1, row_type = 'sub' WHERE section_number IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabus_assessment_tasks', function (Blueprint $table) {
            // Remove new columns
            $table->dropIndex('sat_section_row_pos_idx');
            $table->dropColumn(['section_number', 'row_type', 'section_label']);
        });
    }
};
