<?php
// Підключення всіх файлів
require_once 'lib/functions.php';
require_once 'classes/Student.php';
require_once 'classes/HonorsStudent.php';
require_once 'classes/Gradebook.php';

$gradebook = new Gradebook();

// Створення об'єктів та додавання у менеджер
$gradebook->addStudent(new HonorsStudent('Роман Михаць', 'ІО-44', [98, 95, 100], 2910.00));
$gradebook->addStudent(new Student('Саша', 'ІО-44', [75, 80, 82]));
$gradebook->addStudent(new Student('Олена Коваленко', 'ІО-45', [60, 65, 70]));

$allStudents = $gradebook->getAll();
$topStudent = $gradebook->topStudent();
$groupIO44 = $gradebook->findByGroup('ІО-44');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Лабораторна робота №3</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .highlight { font-weight: bold; color: green; }
    </style>
</head>
<body>
    <h1>Система обліку студентів</h1>

    <h2>Усі студенти (HTML-таблиця)</h2>
    <table>
        <tr>
            <th>Інформація про студента</th>
            <th>Оцінки</th>
            <th>Середній бал</th>
            <th>Рейтинг</th>
        </tr>
        <?php foreach ($allStudents as $student): ?>
        <tr>
            <td><?= $student->getInfo() ?></td>
            <td><?= implode(', ', $student->getGrades()) ?></td>
            <td><?= calcAverage($student->getGrades()) ?></td>
            <td><?= gradeToLetter(calcAverage($student->getGrades())) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Найкращий студент курсу</h2>
    <?php if ($topStudent): ?>
        <p class="highlight">
            <?= $topStudent->getInfo() ?> (Бал: <?= calcAverage($topStudent->getGrades()) ?>)
        </p>
    <?php endif; ?>

    <h2>Фільтр: Група ІО-44</h2>
    <ul>
        <?php foreach ($groupIO44 as $student): ?>
            <li><?= $student->getInfo() ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>