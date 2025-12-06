<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Hash;

$new = 'admin123';
$sa = SuperAdmin::first();
if (!$sa) {
    echo "No SuperAdmin row found. Creating one using .env username...\n";
    $username = env('SUPERADMIN_USERNAME', 'superadmin');
    $sa = SuperAdmin::create([
        'username' => $username,
        'password' => Hash::make($new),
    ]);
    echo "Created SuperAdmin '{$sa->username}'.\n";
} else {
    $sa->password = Hash::make($new);
    $sa->save();
    echo "Updated password for SuperAdmin '{$sa->username}'.\n";
}
echo "Done.\n";