<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = Illuminate\Support\Facades\Schema::getColumnListing('syllabus_assessment_tasks');

echo "Columns in syllabus_assessment_tasks:\n";
foreach ($columns as $column) {
    echo "  - $column\n";
}

// Check specifically for c, p, a
$hasCPA = in_array('c', $columns) && in_array('p', $columns) && in_array('a', $columns);
echo "\nHas C, P, A columns: " . ($hasCPA ? 'YES' : 'NO') . "\n";
