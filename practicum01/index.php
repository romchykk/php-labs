<?php
declare(strict_types=1);

//Налаштування для виведення помилок
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Оголошення масиву даних (Варіант 11)
$students = [
    ['name' => 'Анна Коваленко', 'group' => 'ІМ-31', 'avgGrade' => 96.4],
    ['name' => 'Богдан Мельник', 'group' => 'ІП-51', 'avgGrade' => 87.8],
    ['name' => 'Вікторія Шевченко', 'group' => 'ІО-32', 'avgGrade' => 91.2],
    ['name' => 'Дмитро Бондар', 'group' => 'ІП-42', 'avgGrade' => 78.6]
];

//Типізована функція форматування
function formatStudent(array $student): string {
    return "<strong>{$student['name']}</strong> (Група {$student['group']})";
}

//Обчислення агрегатного показника (середній бал по всій групі)
$totalGrade = 0;
foreach ($students as $student) {
    $totalGrade += $student['avgGrade'];
}
$averageGrade = $totalGrade / count($students);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Облік студентів - Практична 1</title>
</head>
<body>
    <h2>Список студентів</h2>
    <ul>
        <!--Виведення даних через цикл
        <?php foreach ($students as $student): ?>
            <?php 
            //Умовна логіка
            $status = ($student['avgGrade'] >= 90) ? 'Відмінник' : ''; 
            ?>
            <li>
                <?= formatStudent($student) ?> — Середній бал: <?= $student['avgGrade'] ?> 
                <span style="color: green; font-weight: bold;"><?= $status ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Підсумок</h2>
    <p>Середній бал по всій групі: <strong><?= round($averageGrade, 2) ?></strong></p>
</body>
</html>