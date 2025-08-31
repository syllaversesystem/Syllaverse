<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('syllabus_mission_visions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('syllabus_id')->unique();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->timestamps();

            $table->foreign('syllabus_id')->references('id')->on('syllabi')->onDelete('cascade');
        });

        // remove mission/vision from syllabi table (if they exist)
        if (Schema::hasColumn('syllabi', 'mission') || Schema::hasColumn('syllabi', 'vision')) {
            Schema::table('syllabi', function (Blueprint $table) {
                if (Schema::hasColumn('syllabi', 'mission')) {
                    $table->dropColumn('mission');
                }
                if (Schema::hasColumn('syllabi', 'vision')) {
                    $table->dropColumn('vision');
                }
            });
        }
    }

    public function down(): void
    {
        // re-add columns to syllabi table
        if (Schema::hasTable('syllabi')) {
            Schema::table('syllabi', function (Blueprint $table) {
                if (!Schema::hasColumn('syllabi', 'mission')) {
                    $table->text('mission')->nullable();
                }
                if (!Schema::hasColumn('syllabi', 'vision')) {
                    $table->text('vision')->nullable();
                }
            });
        }

        Schema::dropIfExists('syllabus_mission_visions');
    }
};
