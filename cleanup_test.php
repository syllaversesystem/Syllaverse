<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$deleted = \App\Models\Program::where('code', 'TEST123')->delete();
echo "Deleted $deleted test program(s)\n";