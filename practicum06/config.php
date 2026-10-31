<?php
/**
 * config.php — налаштування підключення до БД
 * Практична робота №6, Варіант 11
 */

define('DB_HOST',    'localhost');
define('DB_NAME',    'students_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

function getPdo(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        error_log('DB connection failed: ' . $e->getMessage());
        // Деталі винятку клієнту НЕ передаємо
        echo json_encode(
            ['success' => false, 'error' => 'Не вдалося підключитися до бази даних'],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}