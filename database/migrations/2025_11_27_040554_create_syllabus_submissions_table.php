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
        Schema::create('syllabus_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users');
            $table->enum('from_status', [
                'draft',
                'pending_review',
                'revision',
                'approved',
                'final_approval'
            ]);
            $table->enum('to_status', [
                'draft',
                'pending_review',
                'revision',
                'approved',
                'final_approval'
            ]);
            $table->foreignId('action_by')->constrained('users'); // Who performed the action
            $table->text('remarks')->nullable();
            $table->timestamp('action_at');
            $table->timestamps();
            
            $table->index(['syllabus_id', 'action_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_submissions');
    }
};
