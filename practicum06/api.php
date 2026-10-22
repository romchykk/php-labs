<?php
/**
 * api.php — Єдина точка входу API
 * Практична робота №6, Варіант 11: Система обліку студентів/оцінок
 *
 * Контракт (детальніше — API.md):
 *   GET  /api.php?resource=students           — список усіх студентів
 *   GET  /api.php?resource=students&id=N      — один студент або 404
 *   POST /api.php?resource=students           — створити студента (201)
 *   POST /api.php?resource=students&action=top — студент з найвищим балом у групі
 */

declare(strict_types=1);

// ─── Крок 2. Заголовок JSON для всіх відповідей ────────────────────────────
header('Content-Type: application/json; charset=utf-8');

// Підключення конфігурації та отримання PDO
require_once __DIR__ . '/config.php';
$pdo = getPdo();

// ─── Читання параметрів запиту ──────────────────────────────────────────────
$method   = $_SERVER['REQUEST_METHOD'];   // GET, POST, …
$resource = $_GET['resource'] ?? null;    // students, …
$id       = $_GET['id']       ?? null;    // числовий id або null
$action   = $_GET['action']   ?? null;    // доменна дія або null

// ─── Крок 3. Маршрутизація ──────────────────────────────────────────────────
if ($resource === 'students') {

    if ($method === 'GET' && $id === null) {
        // GET /api.php?resource=students
        listStudents($pdo);

    } elseif ($method === 'GET' && $id !== null) {
        // GET /api.php?resource=students&id=N
        getStudent($pdo, (int) $id);

    } elseif ($method === 'POST' && $action === null) {
        // POST /api.php?resource=students  — створення
        createStudent($pdo);

    } elseif ($method === 'POST' && $action === 'top') {
        // POST /api.php?resource=students&action=top — доменна дія
        topStudentInGroup($pdo);

    } elseif ($method === 'POST') {
        // POST з невідомою action
        sendError(400, "Невідома дія: «{$action}». Підтримується лише action=top");

    } else {
        // Метод не підтримується для ресурсу students (наприклад PUT/DELETE)
        http_response_code(405);
        header('Allow: GET, POST');
        echo json_encode(
            ['success' => false, 'error' => "Метод {$method} не підтримується для ресурсу students"],
            JSON_UNESCAPED_UNICODE
        );
    }

} else {
    // Невідомий ресурс
    sendError(404, 'Невідомий ресурс. Доступні: students');
}

// ════════════════════════════════════════════════════════════════════════════
//  ФУНКЦІЇ-ОБРОБНИКИ
// ════════════════════════════════════════════════════════════════════════════

/**
 * Крок 4а. GET — список усіх студентів.
 * 200 + масив записів.
 */
function listStudents(PDO $pdo): void
{
    $stmt = $pdo->query('SELECT id, name, group_name, average_grade FROM students ORDER BY id');
    $students = $stmt->fetchAll();

    // Числові поля повертаємо як числа, а не рядки
    foreach ($students as &$s) {
        $s['id']            = (int)   $s['id'];
        $s['average_grade'] = (float) $s['average_grade'];
    }
    unset($s);

    sendSuccess(200, $students);
}

/**
 * Крок 4б. GET — один студент за id.
 * 200 + об'єкт або 404.
 */
function getStudent(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare(
        'SELECT id, name, group_name, average_grade FROM students WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    $student = $stmt->fetch();

    if ($student === false) {
        sendError(404, "Студента з id={$id} не знайдено");
    }

    $student['id']            = (int)   $student['id'];
    $student['average_grade'] = (float) $student['average_grade'];

    sendSuccess(200, $student);
}

/**
 * Крок 5. POST — створення нового студента.
 * Обов'язкові поля: name, group_name, average_grade.
 * 201 + створений запис або 400.
 */
function createStudent(PDO $pdo): void
{
    // Підтримуємо як form-data, так і JSON-тіло
    $input = getInput();

    // Перевірка обов'язкових полів
    $required = ['name', 'group_name', 'average_grade'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
            sendError(400, "Відсутнє обов'язкове поле: {$field}");
        }
    }

    $name          = trim($input['name']);
    $groupName     = trim($input['group_name']);
    $averageGrade  = filter_var($input['average_grade'], FILTER_VALIDATE_FLOAT);

    // Додаткова валідація
    if (mb_strlen($name) < 2) {
        sendError(400, "Поле name надто коротке (мінімум 2 символи)");
    }
    if ($averageGrade === false) {
        sendError(400, "Поле average_grade має бути числом");
    }
    if ($averageGrade < 0 || $averageGrade > 100) {
        sendError(400, "Поле average_grade має бути в діапазоні 0–100");
    }

    // INSERT
    $stmt = $pdo->prepare(
        'INSERT INTO students (name, group_name, average_grade)
         VALUES (:name, :group_name, :average_grade)'
    );
    $stmt->execute([
        ':name'          => $name,
        ':group_name'    => $groupName,
        ':average_grade' => $averageGrade,
    ]);

    $newId = (int) $pdo->lastInsertId();

    sendSuccess(201, [
        'id'            => $newId,
        'name'          => $name,
        'group_name'    => $groupName,
        'average_grade' => $averageGrade,
    ]);
}

/**
 * Крок 6. POST action=top — студент з найвищим середнім балом у групі.
 * Обов'язковий параметр: group_name.
 * 200 + об'єкт або 400/404.
 */
function topStudentInGroup(PDO $pdo): void
{
    $input     = getInput();
    $groupName = isset($input['group_name']) ? trim($input['group_name']) : '';

    if ($groupName === '') {
        sendError(400, "Відсутнє обов'язкове поле: group_name");
    }

    $stmt = $pdo->prepare(
        'SELECT id, name, group_name, average_grade
         FROM students
         WHERE group_name = :group_name
         ORDER BY average_grade DESC
         LIMIT 1'
    );
    $stmt->execute([':group_name' => $groupName]);
    $top = $stmt->fetch();

    if ($top === false) {
        sendError(404, "Студентів у групі «{$groupName}» не знайдено");
    }

    $top['id']            = (int)   $top['id'];
    $top['average_grade'] = (float) $top['average_grade'];

    sendSuccess(200, $top);
}

// ════════════════════════════════════════════════════════════════════════════
//  ДОПОМІЖНІ ФУНКЦІЇ
// ════════════════════════════════════════════════════════════════════════════

/**
 * Читає вхідні дані: спочатку $_POST, потім JSON-тіло (php://input).
 * Повертає асоціативний масив.
 */
function getInput(): array
{
    // Якщо є стандартні POST-параметри — використовуємо їх
    if (!empty($_POST)) {
        return $_POST;
    }

    // Інакше намагаємося розпарсити JSON-тіло
    $raw = file_get_contents('php://input');
    if ($raw !== '' && $raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return [];
}

/**
 * Відправляє успішну JSON-відповідь і завершує скрипт.
 *
 * @param int   $code  HTTP-код (200, 201, …)
 * @param mixed $data  Дані для поля "data"
 */
function sendSuccess(int $code, mixed $data): void
{
    http_response_code($code);
    echo json_encode(
        ['success' => true, 'data' => $data],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    exit;
}

/**
 * Відправляє JSON-відповідь з помилкою і завершує скрипт.
 *
 * @param int    $code    HTTP-код (400, 404, 405, …)
 * @param string $message Опис помилки
 */
function sendError(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(
        ['success' => false, 'error' => $message],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    exit;
}
