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
        Schema::table('general_information', function (Blueprint $table) {
            // Add department_id as nullable (NULL = university-wide default)
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('departments')
                  ->onDelete('cascade');
            
            // Add unique constraint: one entry per section per department
            // (section, department_id) must be unique, allowing NULL department_id for globals
            $table->unique(['section', 'department_id'], 'unique_section_per_department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_information', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('unique_section_per_department');
            
            // Drop foreign key and column
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
