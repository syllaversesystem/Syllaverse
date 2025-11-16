<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Syllabus;
use Illuminate\Http\Request;
use App\Http\Controllers\Faculty\Syllabus\SyllabusController;

// create user and syllabus (no factories available)
$user = User::create([ 'name' => 'Test User', 'email' => 'test+' . time() . '@example.com', 'password' => bcrypt('secret') ]);
// ensure a Department and Course exist with required fields
$dept = \App\Models\Department::firstOrCreate(['code' => 'DEPT1'], ['name' => 'Test Dept']);
\App\Models\Course::firstOrCreate(['code' => 'TEST101'], ['title' => 'Test Course', 'department_id' => $dept->id]);
$course = \App\Models\Course::where('code','TEST101')->first();
$syllabus = Syllabus::create([ 'faculty_id' => $user->id, 'title' => 'Test Syllabus ' . time(), 'academic_year' => '2025', 'semester' => '1st', 'year_level' => '1', 'course_id' => $course->id ]);

$request = Request::create('/','POST', [
    'mission' => 'M test',
    'vision' => 'V test',
    'criteria_data' => [
        [
            'key' => 'major_requirements',
            'heading' => 'Major Requirements',
            'value' => [ ['description' => 'Midterm', 'percent' => '20%'] ]
        ]
    ]
]);
// simulate auth
Illuminate\Support\Facades\Auth::login($user);

$ctrl = new SyllabusController();
try {
    $res = $ctrl->update($request, $syllabus->id);
    echo "Controller update executed\n";
    $rows = \App\Models\SyllabusCriteria::where('syllabus_id', $syllabus->id)->get();
    echo "Inserted criteria count: " . $rows->count() . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
