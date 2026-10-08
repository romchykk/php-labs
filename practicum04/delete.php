<?php
// delete.php — Крок 6: видалення запису (DELETE)

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/students.php';

$id = (int) ($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php');
    exit;
}

// Крок 6: deleteStudent($id) → DELETE через підготовлений запит
$affected = deleteStudent($pdo, $id);

$msg = $affected
    ? 'Студента успішно видалено.'
    : 'Студента не знайдено або вже видалено.';

header('Location: index.php?msg=' . urlencode($msg));
exit;
