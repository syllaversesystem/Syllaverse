<?php
// Quick helper to delete a syllabus_sdgs entry: php scripts/delete_sdg_entry.php <syllabus_id> <entry_id>
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the app
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$syllabusId = $argv[1] ?? null;
$entryId = $argv[2] ?? null;
if (!$syllabusId || !$entryId) {
    echo "Usage: php scripts/delete_sdg_entry.php <syllabus_id> <entry_id>\n";
    exit(1);
}

use App\Models\SyllabusSdg;

$entry = SyllabusSdg::where('id', $entryId)->where('syllabus_id', $syllabusId)->first();
if (!$entry) {
    echo "Entry not found for syllabus_id={$syllabusId} id={$entryId}\n";
    exit(2);
}

try {
    $entry->delete();
    echo "Deleted entry id={$entryId} for syllabus_id={$syllabusId}\n";
    exit(0);
} catch (Throwable $e) {
    echo "Delete failed: " . $e->getMessage() . "\n";
    exit(3);
}
