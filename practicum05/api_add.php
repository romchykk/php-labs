<?php
// api_add.php — додає нового студента, повертає JSON зі створеним записом

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Дозволено лише POST-запити'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Підтримуємо два формати: application/x-www-form-urlencoded і application/json
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (str_contains($contentType, 'application/json')) {
    $body = json_decode(file_get_contents('php://input'), true);
    // Захист: json_decode може повернути не масив
    if (!is_array($body)) { $body = []; }
    $rawName   = $body['name']          ?? null;
    $rawGroup  = $body['group_name']    ?? null;
    $rawGrade  = $body['average_grade'] ?? null;
} else {
    $rawName   = $_POST['name']          ?? null;
    $rawGroup  = $_POST['group_name']    ?? null;
    $rawGrade  = $_POST['average_grade'] ?? null;
}

// Захист від масиву замість рядка (Fatal error при trim([...]))
if (!is_string($rawName))  { $rawName  = ''; }
if (!is_string($rawGroup)) { $rawGroup = ''; }

$name        = trim($rawName);
$group_name  = trim($rawGroup);
$average_grade = $rawGrade;

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
} elseif (!is_numeric($average_grade)) {
    $errors[] = 'Середній бал має бути числом';
} else {
    $gradeFloat = round((float)$average_grade, 2);
    // DECIMAL(5,2) вміщує 100.00; бізнес-межа — 100
    if ($gradeFloat < 0 || $gradeFloat > 100) {
        $errors[] = 'Середній бал має бути числом від 0 до 100';
    }
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
        ':average_grade' => $gradeFloat,
    ]);

    $newId = $pdo->lastInsertId();

    $new = $pdo->prepare(
        'SELECT id, name, group_name, average_grade FROM students WHERE id = :id'
    );
    $new->execute([':id' => $newId]);
    $student = $new->fetch();

    http_response_code(201);
    echo json_encode($student, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    // Не повертаємо деталі винятку клієнту
    echo json_encode(['error' => 'Внутрішня помилка сервера. Зверніться до адміністратора.'], JSON_UNESCAPED_UNICODE);
}