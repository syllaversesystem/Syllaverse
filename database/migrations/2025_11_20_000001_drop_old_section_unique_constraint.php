<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drop the old section-only unique constraint since we now have unique_section_per_department
     * which properly handles both university-wide (NULL) and department-specific entries.
     */
    public function up(): void
    {
        Schema::table('general_information', function (Blueprint $table) {
            // Drop the old constraint that only checked section uniqueness
            $table->dropUnique('general_information_section_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_information', function (Blueprint $table) {
            // Restore the old constraint (though this would conflict with department-specific entries)
            $table->unique('section', 'general_information_section_unique');
        });
    }
};
