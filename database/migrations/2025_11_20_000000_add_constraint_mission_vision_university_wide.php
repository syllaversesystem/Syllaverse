<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add check constraint to ensure mission and vision are always university-wide (department_id must be NULL)
     */
    public function up(): void
    {
        // Add check constraint: mission and vision sections must have NULL department_id
        DB::statement("
            ALTER TABLE general_information
            ADD CONSTRAINT chk_mission_vision_university_wide
            CHECK (
                (section NOT IN ('mission', 'vision'))
                OR 
                (section IN ('mission', 'vision') AND department_id IS NULL)
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE general_information DROP CONSTRAINT chk_mission_vision_university_wide');
    }
};
