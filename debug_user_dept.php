<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== User 121 Debug Information ===\n";

$user = User::find(121);
if (!$user) {
    echo "User 121 not found!\n";
    exit(1);
}

echo "User: " . $user->name . " (ID: " . $user->id . ")\n";

$appointments = $user->appointments()->active()->get();
echo "Total appointments: " . $appointments->count() . "\n\n";

foreach ($appointments as $appointment) {
    echo "- Role: " . $appointment->role . "\n";
    echo "  Scope Type: " . $appointment->scope_type . "\n";
    echo "  Scope ID: " . $appointment->scope_id . "\n";
    echo "  Status: " . $appointment->status . "\n\n";
}

// Test the getUserDepartmentId logic
echo "=== Testing getUserDepartmentId Logic ===\n";

// Check if user has any administrative roles
$hasAdministrativeRole = $appointments->contains(function($appointment) {
    return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']);
});

echo "Has administrative role: " . ($hasAdministrativeRole ? 'YES' : 'NO') . "\n";

if ($hasAdministrativeRole) {
    echo "Should allow department selection (return null)\n";
} else {
    // For basic faculty users, get their department from faculty appointment
    $facultyAppointment = $appointments->filter(function($appointment) {
        return $appointment->role === 'FACULTY' && 
               $appointment->scope_type === 'Department' && 
               !empty($appointment->scope_id);
    })->first();
    
    $departmentId = $facultyAppointment ? $facultyAppointment->scope_id : null;
    echo "Faculty department ID: " . ($departmentId ?: 'NULL') . "\n";
    
    if ($facultyAppointment) {
        echo "Faculty appointment found:\n";
        echo "- Scope Type: " . $facultyAppointment->scope_type . "\n";
        echo "- Scope ID: " . $facultyAppointment->scope_id . "\n";
    } else {
        echo "No valid faculty appointment found\n";
    }
}

echo "\n=== Department Information ===\n";
$departments = \App\Models\Department::all();
foreach ($departments as $dept) {
    echo "Department ID " . $dept->id . ": " . $dept->name . " (" . $dept->code . ")\n";
}