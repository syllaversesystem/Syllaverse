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
        Schema::table('student_outcomes', function (Blueprint $table) {
            // Check if foreign key exists before dropping
            $foreignKeyName = $this->getForeignKeyName();
            if ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            }
            
            // Drop the department_id column
            $table->dropColumn('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_outcomes', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
        });
    }
    
    /**
     * Get the foreign key constraint name for department_id if it exists
     */
    private function getForeignKeyName(): ?string
    {
        $results = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'student_outcomes'
              AND COLUMN_NAME = 'department_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        
        return $results[0]->CONSTRAINT_NAME ?? null;
    }
};
