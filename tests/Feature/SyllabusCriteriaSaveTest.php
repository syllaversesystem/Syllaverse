<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Syllabus;
use App\Models\SyllabusCriteria;

class SyllabusCriteriaSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_faculty_can_save_criteria_data()
    {
        // create user and syllabus
        $user = User::factory()->create();
        $syllabus = Syllabus::factory()->create(['faculty_id' => $user->id]);

        $this->actingAs($user);

        $payload = [
            'mission' => 'Test mission',
            'vision' => 'Test vision',
            'criteria_data' => [
                [
                    'key' => 'major_requirements',
                    'heading' => 'Major Requirements',
                    'value' => [
                        ['description' => 'Midterm Exam', 'percent' => '20%'],
                        ['description' => 'Final Exam', 'percent' => '40%']
                    ]
                ],
                [
                    'key' => 'additional',
                    'heading' => 'Additional Requirements',
                    'value' => [
                        ['description' => 'Lab Reports', 'percent' => '40%']
                    ]
                ]
            ]
        ];

        $res = $this->followingRedirects()->post(route('faculty.syllabi.update', $syllabus->id), array_merge($payload, ['_method' => 'PUT']));
        $res->assertStatus(200);

        $this->assertDatabaseHas('syllabus_criteria', [
            'syllabus_id' => $syllabus->id,
            'key' => 'major_requirements',
            'heading' => 'Major Requirements'
        ]);

        $this->assertDatabaseHas('syllabus_criteria', [
            'syllabus_id' => $syllabus->id,
            'key' => 'additional',
            'heading' => 'Additional Requirements'
        ]);

        // assert value JSON contains expected items
        $row = SyllabusCriteria::where('syllabus_id', $syllabus->id)->where('key', 'major_requirements')->first();
        $this->assertNotNull($row);
        $this->assertIsArray($row->value);
        $this->assertCount(2, $row->value);
        $this->assertEquals('Midterm Exam', $row->value[0]['description']);
    }
}
