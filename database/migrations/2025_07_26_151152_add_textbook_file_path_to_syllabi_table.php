<?php

// File: database/migrations/xxxx_xx_xx_xxxxxx_add_textbook_file_path_to_syllabi_table.php
// Description: Adds textbook_file_path column to syllabi table – Syllaverse

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
    Schema::table('syllabi', function (Blueprint $table) {
        $table->string('textbook_file_path')->nullable(); // Remove ->after() if unsure
    });
}


    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->string('textbook_file_path')->nullable()->after('title');

        });
    }
};
