<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tablesToCheck = ['syllabus_criteria', 'syllabus_criterias', 'syllabus_criterion_items'];
foreach ($tablesToCheck as $t) {
    $exists = Schema::hasTable($t) ? 'yes' : 'no';
    echo "$t: $exists\n";
}

// Also list all tables that contain 'syllabus' for context
$rows = DB::select("SHOW TABLES LIKE '%syllabus%'");
echo "\nMatching tables:\n";
foreach ($rows as $r) {
    $vals = array_values((array)$r);
    echo $vals[0] . "\n";
}
