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
            $table->enum('submission_status', [
                'draft',
                'pending_review',
                'revision',
                'approved',
                'final_approval'
            ])->default('draft')->after('year_level');
            
            $table->text('submission_remarks')->nullable()->after('submission_status');
            $table->timestamp('submitted_at')->nullable()->after('submission_remarks');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->after('submitted_at');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'submission_status',
                'submission_remarks',
                'submitted_at',
                'reviewed_by',
                'reviewed_at'
            ]);
        });
    }
};
