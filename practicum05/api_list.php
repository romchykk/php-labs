<?php
// api_list.php — повертає список студентів у форматі JSON
// Підтримує фільтрацію за групою: ?q=ІП-31

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Захист від масиву у GET-параметрі
    $q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';

    if ($q !== '') {
        // Екрануємо спецсимволи LIKE, щоб '%' і '_' шукались буквально
        $q = addcslashes($q, '%_\\');
        $stmt = $pdo->prepare(
            'SELECT id, name, group_name, average_grade
             FROM students
             WHERE group_name LIKE :q'
        );
        $stmt->execute([':q' => '%' . $q . '%']);
    } else {
        $stmt = $pdo->query(
            'SELECT id, name, group_name, average_grade
             FROM students'
        );
    }

    $students = $stmt->fetchAll();
    echo json_encode($students, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    // Не повертаємо деталі винятку клієнту
    echo json_encode(['error' => 'Внутрішня помилка сервера. Зверніться до адміністратора.'], JSON_UNESCAPED_UNICODE);
}