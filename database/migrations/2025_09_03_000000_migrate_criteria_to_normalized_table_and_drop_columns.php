<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ensure normalized table exists before migrating
        if (! Schema::hasTable('syllabus_criteria')) {
            Schema::create('syllabus_criteria', function (Blueprint $table) {
                $table->id();
                $table->foreignId('syllabus_id')->constrained('syllabi')->onDelete('cascade');
                $table->string('key');
                $table->string('heading')->nullable();
                $table->string('section')->nullable();
                $table->json('value')->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();
                $table->unique(['syllabus_id', 'key']);
            });
        }

        // If the course info columns exist, migrate their content into the normalized table
        if (Schema::hasTable('syllabus_course_infos')) {
            $hasLecture = Schema::hasColumn('syllabus_course_infos', 'criteria_lecture');
            $hasLab = Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory');
            if ($hasLecture || $hasLab) {
                $rows = DB::table('syllabus_course_infos')->select('id','syllabus_id','criteria_lecture','criteria_laboratory')->get();
                foreach ($rows as $row) {
                    if ($hasLecture && $row->criteria_lecture) {
                        $values = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $row->criteria_lecture))));
                        if (count($values) > 0) {
                            DB::table('syllabus_criteria')->updateOrInsert([
                                'syllabus_id' => $row->syllabus_id,
                                'key' => 'lecture'
                            ], [
                                'heading' => $values[0] ?? null,
                                'value' => json_encode(array_slice($values,1)),
                                'section' => 'lecture',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    if ($hasLab && $row->criteria_laboratory) {
                        $values = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $row->criteria_laboratory))));
                        if (count($values) > 0) {
                            DB::table('syllabus_criteria')->updateOrInsert([
                                'syllabus_id' => $row->syllabus_id,
                                'key' => 'laboratory'
                            ], [
                                'heading' => $values[0] ?? null,
                                'value' => json_encode(array_slice($values,1)),
                                'section' => 'laboratory',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
                // drop the denormalized columns
                Schema::table('syllabus_course_infos', function (Blueprint $table) use ($hasLecture, $hasLab) {
                    if ($hasLecture && Schema::hasColumn('syllabus_course_infos', 'criteria_lecture')) {
                        $table->dropColumn('criteria_lecture');
                    }
                    if ($hasLab && Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory')) {
                        $table->dropColumn('criteria_laboratory');
                    }
                    if (Schema::hasColumn('syllabus_course_infos', 'criteria_lecture_title')) {
                        $table->dropColumn('criteria_lecture_title');
                    }
                    if (Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory_title')) {
                        $table->dropColumn('criteria_laboratory_title');
                    }
                });
            }
        }
    }

    public function down()
    {
        // recreate the denormalized columns if missing
        if (Schema::hasTable('syllabus_course_infos')) {
            Schema::table('syllabus_course_infos', function (Blueprint $table) {
                if (! Schema::hasColumn('syllabus_course_infos', 'criteria_lecture')) {
                    $table->text('criteria_lecture')->nullable();
                }
                if (! Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory')) {
                    $table->text('criteria_laboratory')->nullable();
                }
                if (! Schema::hasColumn('syllabus_course_infos', 'criteria_lecture_title')) {
                    $table->string('criteria_lecture_title')->nullable();
                }
                if (! Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory_title')) {
                    $table->string('criteria_laboratory_title')->nullable();
                }
            });
        }

        // do not delete normalized table on rollback
    }
};
