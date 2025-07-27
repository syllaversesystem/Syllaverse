<?php
// File: database/migrations/2025_07_28_000001_create_syllabus_sos_table.php
// Description: Stores editable Student Outcomes (SO) specific to each syllabus – Syllaverse

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('syllabus_sos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus_sos');
    }
};
