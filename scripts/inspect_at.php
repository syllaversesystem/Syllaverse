<?php
$db = __DIR__ . '/../database/database.sqlite';
if (!file_exists($db)) {
    echo json_encode(['error' => 'db missing', 'path' => $db]);
    exit(1);
}
try {
    $pdo = new PDO('sqlite:' . $db);
    $sql = "SELECT id, syllabus_id, section, code, task, c, p, a, percent FROM syllabus_assessment_tasks WHERE syllabus_id = 86 ORDER BY id DESC LIMIT 50";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
}
