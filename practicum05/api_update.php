<?php
// api_update.php — оновлює середній бал студента (вимога варіанту №11)

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Дозволено лише POST-запити'], JSON_UNESCAPED_UNICODE);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (str_contains($contentType, 'application/json')) {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) { $body = []; }
    $rawId    = $body['id']            ?? null;
    $rawGrade = $body['average_grade'] ?? null;
} else {
    $rawId    = $_POST['id']            ?? null;
    $rawGrade = $_POST['average_grade'] ?? null;
}

// Валідація
$errors = [];

if (!is_numeric($rawId) || (int)$rawId <= 0) {
    $errors[] = 'Невірний ідентифікатор студента';
}

if ($rawGrade === null || $rawGrade === '') {
    $errors[] = 'Поле "Середній бал" є обов\'язковим';
} elseif (!is_numeric($rawGrade)) {
    $errors[] = 'Середній бал має бути числом';
} else {
    $gradeFloat = round((float)$rawGrade, 2);
    if ($gradeFloat < 0 || $gradeFloat > 100) {
        $errors[] = 'Середній бал має бути від 0 до 100';
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['errors' => $errors], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = (int)$rawId;

try {
    // Перевіряємо що студент існує
    $check = $pdo->prepare('SELECT id FROM students WHERE id = :id');
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Студента не знайдено'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Оновлюємо середній бал
    $stmt = $pdo->prepare(
        'UPDATE students SET average_grade = :grade WHERE id = :id'
    );
    $stmt->execute([':grade' => $gradeFloat, ':id' => $id]);

    // Повертаємо оновлений запис
    $sel = $pdo->prepare(
        'SELECT id, name, group_name, average_grade FROM students WHERE id = :id'
    );
    $sel->execute([':id' => $id]);
    $student = $sel->fetch();

    echo json_encode($student, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Внутрішня помилка сервера. Зверніться до адміністратора.'], JSON_UNESCAPED_UNICODE);
}