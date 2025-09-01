<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SyllabusCriterion;

$rows = SyllabusCriterion::where('syllabus_id', 86)->get();
if ($rows->isEmpty()) {
    echo "No criteria rows for syllabus 86\n";
    exit(0);
}
foreach ($rows as $r) {
    echo "id={$r->id} key={$r->key} heading=" . ($r->heading ?? '') . " value=" . json_encode($r->value) . " pos={$r->position}\n";
}
