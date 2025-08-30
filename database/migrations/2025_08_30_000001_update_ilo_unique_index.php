<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('intended_learning_outcomes', function (Blueprint $table) {
            // Drop the global unique index on `code` and replace with composite unique per course
            try {
                $table->dropUnique('intended_learning_outcomes_code_unique');
            } catch (\Throwable $e) {
                // index may already be changed; ignore
            }

            // Ensure there isn't an existing conflicting index name
            $table->unique(['course_id', 'code'], 'ilo_course_id_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('intended_learning_outcomes', function (Blueprint $table) {
            try {
                $table->dropUnique('ilo_course_id_code_unique');
            } catch (\Throwable $e) {
                // ignore
            }
            $table->unique('code');
        });
    }
};

