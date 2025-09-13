<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // If you used ENUMs before, modify them here
        $table->enum('role', ['DEPT_CHAIR', 'FACULTY'])
                  ->change();

            $table->enum('scope_type', ['Department', 'Program', 'Faculty'])
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Revert to original two options
        $table->enum('role', ['DEPT_CHAIR'])
                  ->change();

            $table->enum('scope_type', ['Department', 'Program'])
                  ->change();
        });
    }
};
