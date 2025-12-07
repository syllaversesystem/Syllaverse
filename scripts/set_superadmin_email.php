<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SuperAdmin;

$email = $argv[1] ?? 'syllaverse.system@gmail.com';
$admin = SuperAdmin::first();
if (!$admin) {
    echo "No SuperAdmin row found. Please create one first (scripts/set_superadmin_password.php).\n";
    exit(1);
}

$admin->email = $email;
$admin->email_verified_at = now();
$admin->save();

echo "Superadmin email set to '{$email}' and verified.\n";