<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if foreign key exists and drop it
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'igas' 
            AND COLUMN_NAME = 'department_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (!empty($foreignKeys)) {
            $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE `igas` DROP FOREIGN KEY `{$constraintName}`");
        }
        
        // Drop the department_id column if it exists
        if (Schema::hasColumn('igas', 'department_id')) {
            Schema::table('igas', function (Blueprint $table) {
                $table->dropColumn('department_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('igas', function (Blueprint $table) {
            // Re-add department_id column
            $table->unsignedBigInteger('department_id')->nullable()->after('description');
            // Re-add foreign key constraint
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }
};
