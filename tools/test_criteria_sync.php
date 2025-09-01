<?php
// tools/test_criteria_sync.php
// Quick verification script: sync criteria and confirm omission doesn't delete existing keys.

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Syllabus;
use App\Models\SyllabusCriterion;

echo "Running criteria sync sanity test...\n";

$s = Syllabus::first();
if (! $s) {
    echo "NO_SYLLABUS\n";
    exit(1);
}

echo "SYLLABUS ID: {$s->id}\n";

// First sync: create lecture + lab
$s->syncCriteriaFromRequest([
    'criteria_lecture' => "Midterm (20%)\nFinal (30%)",
    'criteria_laboratory' => "Lab Report (10%)"
]);

echo "After first sync:\n";
print_r(SyllabusCriterion::where('syllabus_id', $s->id)->get()->toArray());

// Second sync: omit laboratory key (should NOT delete existing lab criterion)
$s->syncCriteriaFromRequest([
    'criteria_lecture' => "Midterm (20%)\nFinal (30%)"
]);

echo "\nAfter second sync (lab omitted):\n";
print_r(SyllabusCriterion::where('syllabus_id', $s->id)->get()->toArray());

echo "Done.\n";

return 0;
