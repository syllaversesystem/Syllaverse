<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('syllabus_sos')) {
            Schema::table('syllabus_sos', function (Blueprint $table) {
                if (!Schema::hasColumn('syllabus_sos', 'title')) {
                    $table->string('title')->nullable()->after('code');
                }
            });
        }
        if (Schema::hasTable('syllabus_cdios')) {
            Schema::table('syllabus_cdios', function (Blueprint $table) {
                if (!Schema::hasColumn('syllabus_cdios', 'title')) {
                    $table->string('title')->nullable()->after('code');
                }
            });
        }
        if (Schema::hasTable('syllabus_igas')) {
            Schema::table('syllabus_igas', function (Blueprint $table) {
                if (!Schema::hasColumn('syllabus_igas', 'title')) {
                    $table->string('title')->nullable()->after('code');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('syllabus_sos')) {
            Schema::table('syllabus_sos', function (Blueprint $table) {
                if (Schema::hasColumn('syllabus_sos', 'title')) {
                    $table->dropColumn('title');
                }
            });
        }
        if (Schema::hasTable('syllabus_cdios')) {
            Schema::table('syllabus_cdios', function (Blueprint $table) {
                if (Schema::hasColumn('syllabus_cdios', 'title')) {
                    $table->dropColumn('title');
                }
            });
        }
        if (Schema::hasTable('syllabus_igas')) {
            Schema::table('syllabus_igas', function (Blueprint $table) {
                if (Schema::hasColumn('syllabus_igas', 'title')) {
                    $table->dropColumn('title');
                }
            });
        }
    }
};
