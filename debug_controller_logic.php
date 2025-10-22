<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Simulate login as user 121
$user = User::find(121);
Auth::login($user);

echo "=== Testing Faculty Program Controller Logic ===\n";

// Mock request object
$request = new \Illuminate\Http\Request();

// Simulate the index method logic
$userAppointments = $user->appointments()->active()->get();

echo "User: " . $user->name . "\n";
echo "Appointments:\n";
foreach ($userAppointments as $appointment) {
    echo "- " . $appointment->role . " (" . $appointment->scope_type . ":" . $appointment->scope_id . ")\n";
}

// Check if user has any administrative roles
$hasAdministrativeRole = $userAppointments->contains(function($appointment) {
    return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']);
});

echo "\nHas administrative role: " . ($hasAdministrativeRole ? 'YES' : 'NO') . "\n";

$showAddDepartmentDropdown = $hasAdministrativeRole;
echo "Show Add Department Dropdown: " . ($showAddDepartmentDropdown ? 'YES' : 'NO') . "\n";

// Get user's department
$userDepartment = null;
if (!$hasAdministrativeRole) {
    $facultyAppointment = $userAppointments->filter(function($appointment) {
        return $appointment->role === 'FACULTY' && 
               $appointment->scope_type === 'Department' && 
               !empty($appointment->scope_id);
    })->first();
    
    if ($facultyAppointment) {
        $userDepartment = $facultyAppointment->scope_id;
    }
}

echo "User Department: " . ($userDepartment ?: 'NULL') . "\n";

// Test the getUserDepartmentId method logic
echo "\n=== Testing getUserDepartmentId Method ===\n";

if ($hasAdministrativeRole) {
    echo "Method should return: NULL (allow department selection)\n";
} else {
    $facultyAppointment = $userAppointments->filter(function($appointment) {
        return $appointment->role === 'FACULTY' && 
               $appointment->scope_type === 'Department' && 
               !empty($appointment->scope_id);
    })->first();
    
    $departmentId = $facultyAppointment ? $facultyAppointment->scope_id : null;
    echo "Method should return: " . ($departmentId ?: 'NULL') . "\n";
}

// Test validation logic
echo "\n=== Testing Validation Logic ===\n";
$getUserDepartmentId = function($user) use ($userAppointments, $hasAdministrativeRole) {
    if ($hasAdministrativeRole) {
        return null;
    }
    
    $facultyAppointment = $userAppointments->filter(function($appointment) {
        return $appointment->role === 'FACULTY' && 
               $appointment->scope_type === 'Department' && 
               !empty($appointment->scope_id);
    })->first();
    
    return $facultyAppointment ? $facultyAppointment->scope_id : null;
};

$userDepartmentId = $getUserDepartmentId($user);
echo "getUserDepartmentId result: " . ($userDepartmentId ?: 'NULL') . "\n";

// Simulate validation rules
$validationRules = [
    'name'        => 'required|string|max:255',
    'description' => 'nullable|string',
    'code'        => 'required|string|max:25',
];

// Only add department_id validation if user doesn't have auto-assigned department
if (!$userDepartmentId) {
    $validationRules['department_id'] = 'required|exists:departments,id';
    echo "Department validation REQUIRED (user must select)\n";
} else {
    echo "Department validation NOT required (auto-assigned to: $userDepartmentId)\n";
}

echo "Final validation rules:\n";
foreach ($validationRules as $field => $rules) {
    echo "- $field: $rules\n";
}