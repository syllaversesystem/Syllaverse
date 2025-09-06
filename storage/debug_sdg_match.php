<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sdg;
use App\Models\Syllabus;

$syllabusId = 118;
$syllabus = Syllabus::with('sdgs')->find($syllabusId);
$sdgs = Sdg::ordered()->get();
if (! $syllabus) { echo "No syllabus $syllabusId\n"; exit(0); }

$attached = collect($syllabus->sdgs ?? [])->map(function($s){
    if (is_array($s)) {
        $id = $s['sdg_id'] ?? ($s['id'] ?? null);
        $title = $s['title'] ?? null;
        $code = $s['code'] ?? null;
        return ['id'=>$id,'title'=>$title,'code'=>$code];
    }
    if (isset($s->pivot)) {
        return ['id'=>$s->id ?? null, 'title'=> ($s->pivot->title ?? $s->title) ?? null, 'code'=> $s->code ?? null];
    }
    if (isset($s->title) && !isset($s->id)) {
        return ['id'=>$s->sdg_id ?? null, 'title'=>$s->title ?? null, 'code'=>$s->code ?? null];
    }
    $id = $s->sdg_id ?? $s->id ?? null;
    $title = $s->title ?? null;
    $code = $s->code ?? null;
    return ['id'=>$id,'title'=>$title,'code'=>$code];
});

$attachedMasterIds = $attached->pluck('id')->filter()->unique()->values();
$attachedTitles = $attached->pluck('title')->filter()->map(function($t){ return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)$t))); })->unique()->values();
$attachedCodes = $attached->pluck('code')->filter()->map(function($c){ return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)$c))); })->unique()->values();

$masterByTitle = collect($sdgs ?? [])->mapWithKeys(function($m){ $k = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)($m->title ?? '')))); return [$k => $m->id ?? null]; });
$masterByCode = collect($sdgs ?? [])->mapWithKeys(function($m){ $k = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)($m->code ?? '')))); return [$k => $m->id ?? null]; });

$resolvedIds = collect();
foreach ($attachedTitles as $t) { if ($masterByTitle->has($t) && $masterByTitle->get($t)) $resolvedIds->push($masterByTitle->get($t)); }
foreach ($attachedCodes as $c) { if ($masterByCode->has($c) && $masterByCode->get($c)) $resolvedIds->push($masterByCode->get($c)); }
$resolvedIds = $resolvedIds->filter()->unique()->values();
$merged = $attachedMasterIds->merge($resolvedIds)->unique()->values();

echo "Syllabus ID: $syllabusId\n\n";
echo "Attached raw:\n"; print_r($attached->toArray());
echo "Attached master ids (raw):\n"; print_r($attachedMasterIds->toArray());
echo "Attached titles (norm):\n"; print_r($attachedTitles->toArray());
echo "Attached codes (norm):\n"; print_r($attachedCodes->toArray());

echo "Master map by title:\n"; print_r($masterByTitle->toArray());
echo "Master map by code:\n"; print_r($masterByCode->toArray());

echo "Resolved ids from titles/codes:\n"; print_r($resolvedIds->toArray());
echo "Merged attached master ids:\n"; print_r($merged->toArray());

// For each master sdg, show if checkbox condition would be true
echo "\nCheckbox decisions (master sdg id => will be checked?):\n";
foreach ($sdgs as $m) {
    $normTitle = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)($m->title ?? ''))));
    $normCode = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)($m->code ?? ''))));
    $would = ($merged->contains($m->id) || $attachedTitles->contains($normTitle) || $attachedCodes->contains($normCode)) ? 'YES' : 'NO';
    echo "{$m->id} ({$m->code} - {$m->title}) => {$would}\n";
}
