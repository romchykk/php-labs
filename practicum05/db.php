<?php
// db.php — підключення до бази даних через PDO
// Змініть значення нижче під своє середовище

define('DB_HOST', 'localhost');
define('DB_NAME', 'students_db');   // назва вашої БД
define('DB_USER', 'root');          // ваш MySQL-користувач
define('DB_PASS', '');              // ваш пароль

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    error_log('DB connection failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Помилка підключення до БД'], JSON_UNESCAPED_UNICODE);
    exit;
}
