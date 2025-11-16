<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SyllabusAssessmentTask;

// Get latest records
$tasks = SyllabusAssessmentTask::orderBy('id', 'desc')->take(5)->get();

echo "Latest 5 assessment tasks:\n\n";
foreach ($tasks as $task) {
    echo "ID: {$task->id}\n";
    echo "  Task: {$task->task}\n";
    echo "  Row Type: {$task->row_type}\n";
    echo "  C: " . ($task->c ?? 'NULL') . "\n";
    echo "  P: " . ($task->p ?? 'NULL') . "\n";
    echo "  A: " . ($task->a ?? 'NULL') . "\n";
    echo "  ---\n";
}
