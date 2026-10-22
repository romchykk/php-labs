<?php
// api_list.php — повертає список студентів у форматі JSON
// Підтримує фільтрацію за групою: ?q=ІП-31

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $q = trim($_GET['q'] ?? '');

    if ($q !== '') {
        // Фільтр за group_name (LIKE, регістронезалежний)
        $stmt = $pdo->prepare(
            'SELECT id, name, group_name, average_grade
             FROM students
             WHERE group_name LIKE :q
             ORDER BY name ASC'
        );
        $stmt->execute([':q' => '%' . $q . '%']);
    } else {
        $stmt = $pdo->query(
            'SELECT id, name, group_name, average_grade
             FROM students
             ORDER BY name ASC'
        );
    }

    $students = $stmt->fetchAll();

    echo json_encode($students, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
