<?php
// Simple helper to inspect syllabus_assessment_tasks rows for a given syllabus id using DB settings from .env
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) { echo "{\"error\":\"env missing\"}\n"; exit(1); }
$env = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$vars = [];
foreach ($env as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (!strpos($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $vars[trim($k)] = trim(trim($v), "\"'");
}
$driver = $vars['DB_CONNECTION'] ?? 'mysql';
$db = $vars['DB_DATABASE'] ?? '';
$host = $vars['DB_HOST'] ?? '127.0.0.1';
$port = $vars['DB_PORT'] ?? '';
$user = $vars['DB_USERNAME'] ?? '';
$pass = $vars['DB_PASSWORD'] ?? '';
$syllabusId = $argv[1] ?? 86;
try {
    if ($driver === 'mysql') {
        $portPart = $port ? ';port='.$port : '';
        $pdo = new PDO("mysql:host={$host}{$portPart};dbname={$db};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } elseif ($driver === 'sqlite') {
        $dbPath = $vars['DB_DATABASE'] ?? __DIR__ . '/../database/database.sqlite';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } else {
        echo json_encode(['error' => 'unsupported driver', 'driver' => $driver]); exit(1);
    }
    $stmt = $pdo->prepare('SELECT id, syllabus_id, section, code, task, c, p, a, percent FROM syllabus_assessment_tasks WHERE syllabus_id = ? ORDER BY id DESC LIMIT 50');
    $stmt->execute([$syllabusId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
}
