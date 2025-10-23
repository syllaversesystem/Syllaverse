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
            // Add department_id foreign key
            $table->foreignId('department_id')->constrained()->after('id');
            
            // Remove code and position columns
            $table->dropColumn(['code', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_outcomes', function (Blueprint $table) {
            // Add back code and position columns
            $table->string('code')->nullable()->after('title');
            $table->unsignedInteger('position')->default(0)->after('description');
            
            // Remove department_id
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};