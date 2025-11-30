<?php

namespace Database\Factories;

use App\Models\SyllabusComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyllabusComment>
 */
class SyllabusCommentFactory extends Factory
{
    protected $model = SyllabusComment::class;

    public function definition(): array
    {
        return [
            'syllabus_id' => 1,
            'partial_key' => $this->faker->randomElement(['course-info','ilo','criteria-assessment','tlas']),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'status' => 'draft',
            'batch' => $this->faker->numberBetween(1, 3),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
