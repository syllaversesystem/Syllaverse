<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('syllabus_sdg', function (Blueprint $table) {
            if (!Schema::hasColumn('syllabus_sdg', 'position')) {
                $table->integer('position')->nullable()->after('description');
            }
            if (!Schema::hasColumn('syllabus_sdg', 'code')) {
                $table->string('code')->nullable()->after('position');
            }
            // Ensure timestamps exist (created_at/updated_at)
            if (!Schema::hasColumn('syllabus_sdg', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('syllabus_sdg', function (Blueprint $table) {
            if (Schema::hasColumn('syllabus_sdg', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('syllabus_sdg', 'position')) {
                $table->dropColumn('position');
            }
            // leaving timestamps alone in down to avoid accidental data loss during rollbacks in dev
        });
    }
};
