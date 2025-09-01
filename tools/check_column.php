<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo 'criteria_lecture column exists: ' . (Schema::hasColumn('syllabus_course_infos','criteria_lecture') ? 'yes' : 'no') . "\n";
