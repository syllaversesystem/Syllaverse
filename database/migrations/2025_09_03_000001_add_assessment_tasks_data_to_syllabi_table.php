<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('syllabi', 'assessment_tasks_data')) {
            Schema::table('syllabi', function (Blueprint $table) {
                // store serialized AT JSON; use text to avoid strict JSON column issues across DB drivers
                // avoid using ->after() for cross-DB compatibility
                $table->text('assessment_tasks_data')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('syllabi', 'assessment_tasks_data')) {
            Schema::table('syllabi', function (Blueprint $table) {
                $table->dropColumn('assessment_tasks_data');
            });
        }
    }
};
