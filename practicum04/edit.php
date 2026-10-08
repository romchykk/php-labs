<?php
// edit.php — Крок 5: форма редагування + обробка UPDATE

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/students.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if (!$id) {
    header('Location: index.php');
    exit;
}

// Завантажити поточні дані (SELECT WHERE id = :id)
$student = getStudentById($pdo, $id);
if (!$student) {
    header('Location: index.php?msg=' . urlencode('Студента не знайдено.'));
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name']          ?? '');
    $group_name    = trim($_POST['group_name']    ?? '');
    $average_grade = trim($_POST['average_grade'] ?? '');

    if ($name === '') {
        $errors[] = 'ПІБ не може бути порожнім.';
    }
    if ($group_name === '') {
        $errors[] = 'Група не може бути порожньою.';
    }
    if (!is_numeric($average_grade) || $average_grade < 0 || $average_grade > 5) {
        $errors[] = 'Середній бал має бути числом від 0 до 5.';
    }

    if (empty($errors)) {
        // Крок 5: updateStudent() → UPDATE через підготовлений запит
        updateStudent($pdo, $id, $name, $group_name, (float) $average_grade);
        header('Location: index.php?msg=' . urlencode('Дані студента оновлено!'));
        exit;
    }

    // Залишаємо введені значення при помилці
    $student['name']          = $name;
    $student['group_name']    = $group_name;
    $student['average_grade'] = $average_grade;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагувати студента</title>
    <style>
        body      { font-family: Arial, sans-serif; max-width: 500px; margin: 30px auto; padding: 0 16px; }
        h1        { color: #2c3e50; }
        label     { display: block; margin-top: 12px; font-weight: bold; }
        input     { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;
                    border: 1px solid #ccc; border-radius: 4px; }
        .btn-save { margin-top: 18px; padding: 10px 22px; background: #3498db; color: #fff;
                    border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        .btn-back { display: inline-block; margin-top: 10px; color: #3498db; }
        .error    { color: red; background: #fdf; border: 1px solid #fcc; padding: 8px;
                    border-radius: 4px; margin-top: 10px; }
    </style>
</head>
<body>
<h1>Редагувати студента #<?= $id ?></h1>

<?php if ($errors): ?>
    <div class="error">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label for="name">ПІБ студента</label>
    <input type="text" id="name" name="name"
           value="<?= htmlspecialchars($student['name']) ?>" required>

    <label for="group_name">Група</label>
    <input type="text" id="group_name" name="group_name"
           value="<?= htmlspecialchars($student['group_name']) ?>" required>

    <label for="average_grade">Середній бал (0–5)</label>
    <input type="number" id="average_grade" name="average_grade"
           step="0.01" min="0" max="5"
           value="<?= htmlspecialchars($student['average_grade']) ?>" required>

    <button type="submit" class="btn-save">Оновити</button>
</form>

<a href="index.php" class="btn-back">← Повернутися до списку</a>
</body>
</html>
