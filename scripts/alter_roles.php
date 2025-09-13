<?php
// Run from project root: php scripts/alter_roles.php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Capsule\Manager as Capsule;

$base = realpath(__DIR__ . '/../');
// Attempt to bootstrap a minimal Laravel DB connection using .env
$dotenv = Dotenv\Dotenv::createImmutable($base);
$dotenv->load();

$config = [
    'driver' => getenv('DB_CONNECTION') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'database' => getenv('DB_DATABASE') ?: 'database',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];

$capsule = new Capsule;
$capsule->addConnection($config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Using DB: " . $config['driver'] . "@" . $config['host'] . " / " . $config['database'] . "\n";
    $capsule->getConnection()->statement('ALTER TABLE chair_requests MODIFY requested_role VARCHAR(64) NOT NULL');
    echo "chair_requests updated\n";
    $capsule->getConnection()->statement('ALTER TABLE appointments MODIFY role VARCHAR(64) NOT NULL');
    echo "appointments.role updated\n";
    $capsule->getConnection()->statement('ALTER TABLE appointments MODIFY scope_type VARCHAR(64) NOT NULL');
    echo "appointments.scope_type updated\n";
    echo "Done\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
