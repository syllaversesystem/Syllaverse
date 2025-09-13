<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Make `created_by` nullable and change FK to set NULL on delete
        if (Schema::hasColumn('programs', 'created_by')) {
            Schema::table('programs', function (Blueprint $table) {
                // Try to drop foreign key if it exists. Some environments name constraints differently;
                // we'll attempt the common drop and ignore failures.
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Exception $e) {
                    // ignore: constraint may not exist or have an unexpected name
                }

                // Make column nullable (best-effort; some DBs allow change())
                try {
                    $table->unsignedBigInteger('created_by')->nullable()->change();
                } catch (\Exception $e) {
                    // ignore: some drivers (sqlite in memory) can't change column types easily
                }

                // Re-add foreign key with nullOnDelete behavior
                try {
                    $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                } catch (\Exception $e) {
                    // ignore: if adding FK fails, we'll still have nullable created_by
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('programs', 'created_by')) {
            Schema::table('programs', function (Blueprint $table) {
                // Drop modified foreign
                $table->dropForeign(['created_by']);

                // Make column NOT NULL (best-effort)
                $table->unsignedBigInteger('created_by')->nullable(false)->change();

                // Recreate cascading FK
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};
