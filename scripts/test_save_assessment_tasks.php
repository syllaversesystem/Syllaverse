<?php
// Simple script to test saveAssessmentTasks controller method without HTTP server.
// Run: php scripts/test_save_assessment_tasks.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// bootstrap kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// login as the owner of syllabus 87 (found via inspect script)
Auth::loginUsingId(41);

$syllabusId = 87; // syllabus id to test

$payload = ['rows' => [
    ['section' => 'MAP TEST', 'code' => 'MT01', 'task' => 'Mapping test', 'ird' => 'I', 'percent' => 10, 'ilo_flags' => [], 'c' => 'x', 'p' => null, 'a' => null]
]];

$request = Request::create('/faculty/syllabi/' . $syllabusId . '/assessment-tasks', 'POST', $payload);

$controller = new App\Http\Controllers\Faculty\SyllabusController();

$response = app()->call([$controller, 'saveAssessmentTasks'], ['request' => $request, 'syllabus' => $syllabusId]);

echo "Response status: " . ($response->getStatusCode() ?? 'n/a') . PHP_EOL;
echo "Response body: " . $response->getContent() . PHP_EOL;

// Check DB count
$count = App\Models\SyllabusAssessmentTask::where('syllabus_id', $syllabusId)->where('code', 'MT01')->count();
echo "Persisted rows with code MT01: " . $count . PHP_EOL;

