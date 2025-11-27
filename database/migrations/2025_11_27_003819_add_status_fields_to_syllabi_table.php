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
        Schema::table('syllabi', function (Blueprint $table) {
            $table->string('prepared_by_name')->nullable();
            $table->string('prepared_by_title')->nullable();
            $table->date('prepared_by_date')->nullable();
            
            $table->string('reviewed_by_name')->nullable();
            $table->string('reviewed_by_title')->nullable();
            $table->date('reviewed_by_date')->nullable();
            
            $table->string('approved_by_name')->nullable();
            $table->string('approved_by_title')->nullable();
            $table->date('approved_by_date')->nullable();
            
            $table->text('status_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropColumn([
                'prepared_by_name',
                'prepared_by_title',
                'prepared_by_date',
                'reviewed_by_name',
                'reviewed_by_title',
                'reviewed_by_date',
                'approved_by_name',
                'approved_by_title',
                'approved_by_date',
                'status_remarks'
            ]);
        });
    }
};
