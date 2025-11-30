<?php
// Usage (Windows PowerShell): php scripts/assign_dept_head.php user@example.com 5
// Creates or updates an active Department Head appointment for given user + department.

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 3) {
    echo "\nAssign Dept Head Appointment\n";
    echo "Syntax: php scripts/assign_dept_head.php <user_email> <department_id>\n";
    exit(1);
}

$email = $argv[1];
$deptId = (int)$argv[2];

$user = User::where('email', $email)->first();
if (!$user) {
    echo "User not found for email: {$email}\n";
    exit(1);
}

try {
    DB::beginTransaction();

    // End any existing active dept chair/head appointments for this department
    Appointment::where('user_id', $user->id)
        ->where('scope_id', $deptId)
        ->whereIn('role', [Appointment::ROLE_DEPT, Appointment::ROLE_DEPT_HEAD])
        ->where('status', 'active')
        ->get()
        ->each(function($appt){ $appt->endNow(); });

    $appt = new Appointment();
    $appt->user_id = $user->id;
    $appt->role = Appointment::ROLE_DEPT_HEAD; // preferred constant
    $appt->scope_id = $deptId;
    $appt->status = 'active';
    $appt->start_at = now();
    $appt->assigned_by = $user->id; // self-assigned for script; adjust as needed
    $appt->save();

    DB::commit();
    echo "Assigned Department Head role to user {$user->email} for department {$deptId}.\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo "Failed assigning role: " . $e->getMessage() . "\n";
    exit(1);
}
