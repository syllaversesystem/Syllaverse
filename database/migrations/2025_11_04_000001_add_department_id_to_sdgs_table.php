<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sdgs', function (Blueprint $table) {
            if (!Schema::hasColumn('sdgs', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('sort_order');
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
                $table->index('department_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sdgs', function (Blueprint $table) {
            if (Schema::hasColumn('sdgs', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
};
