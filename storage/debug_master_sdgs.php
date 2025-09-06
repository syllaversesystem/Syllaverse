<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sdg;
$all = Sdg::ordered()->get(['id','code','title'])->map(function($s){ return $s->toArray(); });
echo json_encode($all, JSON_PRETTY_PRINT);
