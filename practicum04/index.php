<?php
// index.php — Крок 3: виведення всіх записів (SELECT)

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/students.php';

// Пошук за групою (якщо передано)
$filterGroup = trim($_GET['group'] ?? '');
$students    = $filterGroup
    ? findByGroup($pdo, $filterGroup)
    : getAllStudents($pdo);

$top = topStudent($pdo);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Система обліку студентів</title>
    <style>
        body       { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 16px; }
        h1         { color: #2c3e50; }
        table      { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td     { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th         { background: #2c3e50; color: #fff; }
        tr:hover   { background: #f0f4f8; }
        .top-box   { background: #eafaf1; border: 1px solid #2ecc71; padding: 10px 16px;
                     border-radius: 6px; margin-bottom: 20px; }
        .btn       { padding: 5px 12px; border: none; border-radius: 4px; cursor: pointer;
                     font-size: 0.9em; text-decoration: none; display: inline-block; }
        .btn-edit  { background: #3498db; color: #fff; }
        .btn-del   { background: #e74c3c; color: #fff; }
        .btn-add   { background: #2ecc71; color: #fff; font-size: 1em; padding: 8px 18px; }
        .search    { display: flex; gap: 8px; margin-bottom: 12px; }
        .search input { padding: 7px; border: 1px solid #ccc; border-radius: 4px; width: 220px; }
        .search button { padding: 7px 14px; border: none; background: #2c3e50; color:#fff;
                         border-radius: 4px; cursor: pointer; }
        .msg-ok    { color: green; font-weight: bold; }
    </style>
</head>
<body>
<h1>Система обліку студентів/оцінок</h1>

<?php if (!empty($_GET['msg'])): ?>
    <p class="msg-ok"><?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>

<!-- Топ-студент -->
<?php if ($top): ?>
<div class="top-box">
    🏆 <strong>Найкращий студент:</strong>
    <?= htmlspecialchars($top['name']) ?>
    (<?= htmlspecialchars($top['group_name']) ?>)
    — середній бал: <strong><?= number_format($top['average_grade'], 2) ?></strong>
</div>
<?php endif; ?>

<!-- Пошук за групою -->
<form method="get" class="search">
    <input type="text" name="group" placeholder="Фільтр за групою"
           value="<?= htmlspecialchars($filterGroup) ?>">
    <button type="submit">Знайти</button>
    <?php if ($filterGroup): ?>
        <a href="index.php" class="btn" style="background:#95a5a6;color:#fff">Скинути</a>
    <?php endif; ?>
</form>

<a href="add.php" class="btn btn-add">＋ Додати студента</a>

<!-- Таблиця студентів (Крок 3: fetchAll + HTML-таблиця) -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>ПІБ</th>
            <th>Група</th>
            <th>Середній бал</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($students): ?>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= (int) $s['id'] ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['group_name']) ?></td>
            <td><?= number_format((float) $s['average_grade'], 2) ?></td>
            <td>
                <a href="edit.php?id=<?= (int) $s['id'] ?>" class="btn btn-edit">✏ Редагувати</a>
                <a href="delete.php?id=<?= (int) $s['id'] ?>" class="btn btn-del"
                   onclick="return confirm('Видалити студента «<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>»?')">
                   🗑 Видалити
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="5" style="text-align:center;color:#999">Записів не знайдено</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</body>
</html>
