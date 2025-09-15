<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate controller context for syllabus id 139 (exists in DB from previous dump)
$sy = \App\Models\Syllabus::with(['ilos','sos','igas','cdios','iloIga'])->find(139);
if (! $sy) {
    echo "Syllabus 139 not found\n";
    exit(1);
}

// Share variables like controller's show method would
$programs = \App\Models\Program::all();
$courses = \App\Models\Course::all();
$sdgs = \App\Models\Sdg::all();

// Render the partial into a variable
$view = view('faculty.syllabus.partials.ilo-iga-mapping', ['syllabus' => $sy, 'igas' => $sy->igas])->render();

// Output first 4000 characters to avoid huge dump
echo substr($view, 0, 4000);
