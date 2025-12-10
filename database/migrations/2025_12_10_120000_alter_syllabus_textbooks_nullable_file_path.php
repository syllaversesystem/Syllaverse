<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('syllabus_textbooks', function (Blueprint $table) {
            // Make file_path nullable to support text-only references
            $table->string('file_path')->nullable()->change();
            // Allow longer citation names if needed
            $table->string('original_name', 1000)->change();
        });
    }

    public function down(): void {
        Schema::table('syllabus_textbooks', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
            $table->string('original_name', 255)->change();
        });
    }
};
