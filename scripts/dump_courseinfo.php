<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = $argv[1] ?? 86;
$si = \App\Models\SyllabusCourseInfo::where('syllabus_id', $id)->first();
if (!$si) {
    echo "No SyllabusCourseInfo row for syllabus_id={$id}\n";
    exit(0);
}
echo "SyllabusCourseInfo for syllabus_id={$id}\n";
echo "tla_strategies:\n";
var_export($si->tla_strategies);
echo "\n--- full row ---\n";
print_r($si->toArray());
