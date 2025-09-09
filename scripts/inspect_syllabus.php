<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\Syllabus::first();
if (! $s) {
    echo "no syllabus\n";
    exit(0);
}

echo "id={$s->id} faculty_id={$s->faculty_id}\n";
