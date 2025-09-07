<?php
// Quick test script: boots the framework and creates a syllabus to verify policy seeding
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Course;
use App\Models\Syllabus;

// find a faculty user and a course
$user = User::first();
$course = Course::first();

if (!$user || !$course) {
    echo "Missing User or Course - cannot run test.\n";
    exit(1);
}

$s = Syllabus::create([
    'faculty_id' => $user->id,
    'course_id' => $course->id,
    'title' => 'Test Syllabus ' . time(),
    'academic_year' => date('Y'),
    'semester' => '1st',
    'year_level' => 1,
]);

echo "Created syllabus {$s->id}\n";
$s->load('policies');
foreach ($s->policies as $p) {
    echo "- {$p->section}: " . (strlen($p->content) ? substr($p->content,0,60) : '[empty]') . "\n";
}

echo "Done\n";
