<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Faculty\ProgramController;

echo "=== Direct Controller Test ===\n";

// Login as user 121
$user = User::find(121);
Auth::login($user);

echo "Logged in as: " . $user->name . "\n";

// Create a mock request with test data
$requestData = [
    'name' => 'Test Program Name',
    'code' => 'TEST123',
    'description' => 'Test program description',
    // Note: NO department_id since it should be auto-assigned
];

echo "Request data:\n";
foreach ($requestData as $key => $value) {
    echo "  $key: $value\n";
}

// Create request object
$request = Request::create('/faculty/programs', 'POST', $requestData);
$request->headers->set('Accept', 'application/json');

echo "\n=== Calling ProgramController->store() ===\n";

try {
    $controller = new ProgramController();
    $response = $controller->store($request);
    
    echo "Response type: " . get_class($response) . "\n";
    
    if (method_exists($response, 'getStatusCode')) {
        echo "Status code: " . $response->getStatusCode() . "\n";
    }
    
    if (method_exists($response, 'getContent')) {
        $content = $response->getContent();
        echo "Response content: " . $content . "\n";
        
        $data = json_decode($content, true);
        if ($data) {
            echo "Parsed JSON:\n";
            echo "  Message: " . ($data['message'] ?? 'N/A') . "\n";
            if (isset($data['program'])) {
                echo "  Program ID: " . ($data['program']['id'] ?? 'N/A') . "\n";
                echo "  Program Name: " . ($data['program']['name'] ?? 'N/A') . "\n";
                echo "  Program Code: " . ($data['program']['code'] ?? 'N/A') . "\n";
                echo "  Department ID: " . ($data['program']['department_id'] ?? 'N/A') . "\n";
            }
            if (isset($data['errors'])) {
                echo "  Errors:\n";
                foreach ($data['errors'] as $field => $fieldErrors) {
                    echo "    $field: " . implode(', ', $fieldErrors) . "\n";
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Check if program was actually created
echo "\n=== Checking Database ===\n";
$program = \App\Models\Program::where('code', 'TEST123')->first();
if ($program) {
    echo "Program found in database:\n";
    echo "  ID: " . $program->id . "\n";
    echo "  Name: " . $program->name . "\n";
    echo "  Code: " . $program->code . "\n";
    echo "  Department ID: " . $program->department_id . "\n";
    echo "  Status: " . $program->status . "\n";
    echo "  Created by: " . $program->created_by . "\n";
} else {
    echo "Program NOT found in database\n";
}