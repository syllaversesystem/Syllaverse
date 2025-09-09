<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// find a syllabus to test
$sy = App\Models\Syllabus::first();
if (! $sy) { echo "no syllabus\n"; exit(0); }

// login as its owner
Auth::loginUsingId($sy->faculty_id);

$syllabusId = $sy->id;
$mappings = [
    ['name' => 'Map Test 1', 'week_marks' => ['1-2','4'] ],
    ['name' => 'Map Test 2', 'week_marks' => ['6','8'] ],
];

$request = Request::create('/faculty/syllabi/' . $syllabusId . '/assessment-mappings', 'POST', ['mappings' => $mappings]);
$controller = new App\Http\Controllers\Faculty\SyllabusController();
$response = app()->call([$controller, 'saveAssessmentMappings'], ['request' => $request, 'syllabus' => $syllabusId]);

echo "Response: " . $response->getContent() . PHP_EOL;
$count = App\Models\SyllabusAssessmentMapping::where('syllabus_id', $syllabusId)->count();
echo "Mapping rows now: " . $count . PHP_EOL;
