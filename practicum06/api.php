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
 *
 * Межі: average_grade 0–100; name ≤ 120 символів, group_name ≤ 60 (див. schema.sql)
 */

declare(strict_types=1);

// Глобальний обробник необроблених винятків — гарантує JSON навіть при PDOException
// Попередження/notice не повинні потрапляти у відповідь як HTML зі шляхами файлів
ini_set('display_errors', '0');

set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    // Заголовок міг ще не бути встановлений
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    http_response_code(500);
    // Деталі помилки НЕ передаємо клієнту — лише загальне повідомлення
    echo json_encode(
        ['success' => false, 'error' => 'Внутрішня помилка сервера'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
});

// ─── Заголовок JSON для всіх відповідей ────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
$pdo = getPdo();

// ─── Читання параметрів запиту ──────────────────────────────────────────────
$method   = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? null;
$id       = $_GET['id']       ?? null;
$action   = $_GET['action']   ?? null;

// Параметри маршрутизації мають бути рядками (захист від ?action[]=x)
foreach ([$resource, $id, $action] as $param) {
    if ($param !== null && !is_string($param)) {
        sendError(400, 'Параметри запиту мають бути рядками');
    }
}

// ─── Маршрутизація ──────────────────────────────────────────────────────────
if ($resource === 'students') {

    if ($method === 'GET' && $id === null) {
        listStudents($pdo);

    } elseif ($method === 'GET' && $id !== null) {
        $validId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($validId === false) {
            sendError(400, 'Параметр id має бути додатним цілим числом');
        }
        getStudent($pdo, $validId);

    } elseif ($method === 'POST' && $action === null) {
        createStudent($pdo);

    } elseif ($method === 'POST' && $action === 'top') {
        topStudentInGroup($pdo);

    } elseif ($method === 'POST') {
        sendError(400, "Невідома дія: «{$action}». Підтримується лише action=top");

    } else {
        http_response_code(405);
        header('Allow: GET, POST');
        echo json_encode(
            ['success' => false, 'error' => "Метод {$method} не підтримується для ресурсу students"],
            JSON_UNESCAPED_UNICODE
        );
    }

} else {
    sendError(404, 'Невідомий ресурс. Доступні: students');
}

// ════════════════════════════════════════════════════════════════════════════
//  ФУНКЦІЇ-ОБРОБНИКИ
// ════════════════════════════════════════════════════════════════════════════

function listStudents(PDO $pdo): void
{
    $stmt     = $pdo->query('SELECT id, name, group_name, average_grade FROM students ORDER BY id');
    $students = $stmt->fetchAll();

    foreach ($students as &$s) {
        $s['id']            = (int)   $s['id'];
        $s['average_grade'] = (float) $s['average_grade'];
    }
    unset($s);

    sendSuccess(200, $students);
}

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

function createStudent(PDO $pdo): void
{
    $input = getInput();

    // Перевірка що поля є рядками, а не масивами
    foreach (['name', 'group_name', 'average_grade'] as $field) {
        if (!isset($input[$field])) {
            sendError(400, "Відсутнє обов'язкове поле: {$field}");
        }
        if (is_array($input[$field])) {
            sendError(400, "Поле {$field} має бути рядком, а не масивом");
        }
    }

    $name      = trim((string) $input['name']);
    $groupName = trim((string) $input['group_name']);

    // Валідація name
    if ($name === '') {
        sendError(400, "Відсутнє обов'язкове поле: name");
    }
    if (mb_strlen($name) < 2) {
        sendError(400, "Поле name надто коротке (мінімум 2 символи)");
    }
    if (mb_strlen($name) > 120) {
        sendError(400, "Поле name надто довге (максимум 120 символів)");
    }

    // Валідація group_name
    if ($groupName === '') {
        sendError(400, "Відсутнє обов'язкове поле: group_name");
    }
    if (mb_strlen($groupName) > 60) {
        sendError(400, "Поле group_name надто довге (максимум 60 символів)");
    }

    // Валідація average_grade
    $averageGrade = filter_var($input['average_grade'], FILTER_VALIDATE_FLOAT);
    if ($averageGrade === false) {
        sendError(400, "Поле average_grade має бути числом");
    }
    if ($averageGrade < 0 || $averageGrade > 100) {
        sendError(400, "Поле average_grade має бути в діапазоні 0–100");
    }
    // Те саме значення, що буде збережене в DECIMAL(5,2)
    $averageGrade = round($averageGrade, 2);

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

function topStudentInGroup(PDO $pdo): void
{
    $input = getInput();

    if (!isset($input['group_name'])) {
        sendError(400, "Відсутнє обов'язкове поле: group_name");
    }
    if (is_array($input['group_name'])) {
        sendError(400, "Поле group_name має бути рядком, а не масивом");
    }

    $groupName = trim((string) $input['group_name']);

    if ($groupName === '') {
        sendError(400, "Відсутнє обов'язкове поле: group_name");
    }

    $stmt = $pdo->prepare(
        'SELECT id, name, group_name, average_grade
         FROM students
         WHERE group_name = :group_name
         ORDER BY average_grade DESC, id ASC
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

function getInput(): array
{
    if (!empty($_POST)) {
        return $_POST;
    }

    $raw = file_get_contents('php://input');
    if ($raw !== '' && $raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return [];
}

function sendSuccess(int $code, mixed $data): void
{
    http_response_code($code);
    echo json_encode(
        ['success' => true, 'data' => $data],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function sendError(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(
        ['success' => false, 'error' => $message],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}