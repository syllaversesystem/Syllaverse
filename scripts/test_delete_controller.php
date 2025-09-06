<?php
// Test script: call SyllabusSdgController::destroyEntry and print response
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Faculty\SyllabusSdgController;
use App\Models\Syllabus;

$syllabus = Syllabus::find(117);
if (!$syllabus) {
    echo "Syllabus 117 not found\n";
    exit(1);
}

$controller = new SyllabusSdgController();
// Try deleting entry id 2 (adjust if needed)
$response = $controller->destroyEntry($syllabus, 2);
if (method_exists($response, 'getStatusCode')) {
    echo "Status: " . $response->getStatusCode() . "\n";
}
if (method_exists($response, 'getContent')) {
    echo "Content: " . $response->getContent() . "\n";
} else {
    // fallback for JsonResponse
    if ($response instanceof Illuminate\Http\JsonResponse) {
        echo "Body: " . json_encode($response->getData()) . "\n";
    } else {
        echo "Response type: " . get_class($response) . "\n";
        var_export($response);
    }
}
