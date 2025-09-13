<?php
require __DIR__ . '/../vendor/autoload.php';
$base = realpath(__DIR__ . '/../');
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

use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
$capsule->addConnection($config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$rows = $capsule->getConnection()->select("SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('chair_requests','appointments') AND COLUMN_NAME IN ('requested_role','role','scope_type') ORDER BY TABLE_NAME,COLUMN_NAME");
foreach ($rows as $r) {
    echo "$r->TABLE_NAME.$r->COLUMN_NAME => $r->COLUMN_TYPE\n";
}
