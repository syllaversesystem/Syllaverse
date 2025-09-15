<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = \App\Models\SyllabusIloIga::limit(10)->get(['id','syllabus_id','ilo_text','igas','position']);
$out = [];
foreach ($rows as $r) {
    $out[] = ['id' => $r->id, 'syllabus_id' => $r->syllabus_id, 'ilo_text' => $r->ilo_text, 'igas' => $r->igas, 'position' => $r->position];
}
echo json_encode($out, JSON_PRETTY_PRINT);
