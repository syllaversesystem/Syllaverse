<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SyllabusCriteria;

$syllabusId = $argv[1] ?? 86;
$rows = SyllabusCriteria::where('syllabus_id', $syllabusId)->get();
echo json_encode($rows->map->toArray(), JSON_PRETTY_PRINT);
