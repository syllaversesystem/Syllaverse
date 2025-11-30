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
            if (!Schema::hasColumn('syllabi', 'faculty_id')) {
                $table->unsignedBigInteger('faculty_id')->nullable()->after('id');
                $table->foreign('faculty_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            if (Schema::hasColumn('syllabi', 'faculty_id')) {
                // Drop FK first then the column
                try { $table->dropForeign(['faculty_id']); } catch (\Throwable $e) {}
                $table->dropColumn('faculty_id');
            }
        });
    }
};
