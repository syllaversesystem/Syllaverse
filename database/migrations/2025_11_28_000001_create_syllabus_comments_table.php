<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('syllabus_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('syllabus_id');
            $table->string('partial_key', 64); // e.g., 'course-info', 'ilo'
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('status', 24)->default('draft'); // draft|submitted|resolved
            $table->unsignedInteger('batch')->default(1); // review cycle number
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['syllabus_id', 'partial_key', 'batch']);
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus_comments');
    }
};
