<?php
// Temporary debug script: prints JSON of the latest syllabus's sdgs collection.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Syllabus;

$s = Syllabus::with('sdgs')->latest()->first();
if (! $s) {
    echo json_encode(['error' => 'no syllabus found'], JSON_PRETTY_PRINT);
    exit(0);
}
$out = $s->sdgs->map(function($i){
    if (is_object($i)) {
        if (method_exists($i, 'toArray')) return $i->toArray();
        return (array)$i;
    }
    return $i;
})->values()->all();

echo json_encode(['syllabus_id' => $s->id, 'sdgs' => $out], JSON_PRETTY_PRINT);
