<?php
// api_add.php — додає нового студента, повертає JSON зі створеним записом

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

// Дозволяємо POST-запити з того самого Origin (CORS для локальної розробки)
// header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Дозволено лише POST-запити'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Підтримуємо два формати: application/x-www-form-urlencoded і application/json
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (str_contains($contentType, 'application/json')) {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $name          = trim($body['name']          ?? '');
    $group_name    = trim($body['group_name']    ?? '');
    $average_grade = $body['average_grade']      ?? null;
} else {
    $name          = trim($_POST['name']          ?? '');
    $group_name    = trim($_POST['group_name']    ?? '');
    $average_grade = $_POST['average_grade']      ?? null;
}

// Валідація
$errors = [];
if ($name === '') {
    $errors[] = 'Поле "ПІБ студента" є обов\'язковим';
}
if ($group_name === '') {
    $errors[] = 'Поле "Група" є обов\'язковим';
}
if ($average_grade === null || $average_grade === '') {
    $errors[] = 'Поле "Середній бал" є обов\'язковим';
} elseif (!is_numeric($average_grade) || $average_grade < 0 || $average_grade > 100) {
    $errors[] = 'Середній бал має бути числом від 0 до 100';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['errors' => $errors], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO students (name, group_name, average_grade)
         VALUES (:name, :group_name, :average_grade)'
    );
    $stmt->execute([
        ':name'          => $name,
        ':group_name'    => $group_name,
        ':average_grade' => round((float)$average_grade, 2),
    ]);

    $newId = $pdo->lastInsertId();

    // Повертаємо щойно доданий запис
    $new = $pdo->prepare(
        'SELECT id, name, group_name, average_grade FROM students WHERE id = :id'
    );
    $new->execute([':id' => $newId]);
    $student = $new->fetch();

    http_response_code(201);
    echo json_encode($student, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
