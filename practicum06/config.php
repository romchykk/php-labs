<?php
/**
 * config.php — налаштування підключення до БД
 * Практична робота №6, Варіант 11
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'students_db');
define('DB_USER', 'root');      // замініть на свого користувача
define('DB_PASS', '');          // замініть на свій пароль
define('DB_CHARSET', 'utf8mb4');

/**
 * Повертає PDO-з'єднання або завершує виконання скрипта з JSON-помилкою.
 */
function getPdo(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Помилка підключення — повертаємо 500
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error'   => 'Помилка підключення до бази даних: ' . $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
