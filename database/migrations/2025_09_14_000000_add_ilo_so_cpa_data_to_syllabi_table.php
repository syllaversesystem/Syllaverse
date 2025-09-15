<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('syllabi', 'ilo_so_cpa_data')) {
            Schema::table('syllabi', function (Blueprint $table) {
                $table->text('ilo_so_cpa_data')->nullable()->after('assessment_tasks_data');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('syllabi', 'ilo_so_cpa_data')) {
            Schema::table('syllabi', function (Blueprint $table) {
                $table->dropColumn('ilo_so_cpa_data');
            });
        }
    }
};
