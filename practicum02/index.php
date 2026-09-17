<?php
// Крок 3. Реалізувати серверну обробку й валідацію
$errors = [];
$successMessage = '';

// Отримуємо дані з форми (або порожні рядки, якщо форму ще не відправлено)
$studentName = $_POST['studentName'] ?? '';
$group = $_POST['group'] ?? '';
$subject = $_POST['subject'] ?? '';
$grade = $_POST['grade'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валідація імені студента
    if (trim($studentName) === '') {
        $errors['studentName'] = 'Ім\'я студента є обов\'язковим.';
    }
    
    // Валідація групи
    if (trim($group) === '') {
        $errors['group'] = 'Оберіть групу.';
    }

    // Валідація предмету
    if (trim($subject) === '') {
        $errors['subject'] = 'Назва предмету є обов\'язковою.';
    }

    // Валідація оцінки (число в межах 0-100)
    if (!is_numeric($grade) || $grade < 0 || $grade > 100) {
        $errors['grade'] = 'Оцінка має бути числом від 0 до 100.';
    }

    // Крок 4. Показати результат обробки
    if (empty($errors)) {
        $successMessage = "Дані успішно збережено: " . 
                          htmlspecialchars($studentName) . " (" . 
                          htmlspecialchars($group) . "), " . 
                          htmlspecialchars($subject) . " - " . 
                          htmlspecialchars($grade) . " балів.";
        // Очищаємо поля після успішного збереження 
        $studentName = $subject = $grade = '';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система обліку студентів/оцінок</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 20px auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 8px; box-sizing: border-box; }
        .error { color: red; font-size: 0.9em; margin-top: 5px; display: block; }
        .success { color: green; font-weight: bold; padding: 10px; border: 1px solid green; margin-bottom: 20px; background: #e8f5e9; }
    </style>
</head>
<body>

    <h2>Додавання оцінки</h2>

    <?php if ($successMessage): ?>
        <div class="success"><?= $successMessage ?></div>
    <?php endif; ?>

    <!-- Крок 2. Створити HTML-форму -->
    <form id="gradeForm" method="post" action="index.php" novalidate>
        
        <div class="form-group">
            <label for="studentName">Ім'я студента:</label>
            <input type="text" id="studentName" name="studentName" required 
                   value="<?= htmlspecialchars($studentName) ?>">
            <?php if (isset($errors['studentName'])): ?>
                <span class="error"><?= $errors['studentName'] ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="group">Група:</label>
            <select id="group" name="group" required>
                <option value="">Оберіть групу...</option>
                <option value="Група 1" <?= $group === 'Група 1' ? 'selected' : '' ?>>Група 1</option>
                <option value="Група 2" <?= $group === 'Група 2' ? 'selected' : '' ?>>Група 2</option>
                <option value="Група 3" <?= $group === 'Група 3' ? 'selected' : '' ?>>Група 3</option>
            </select>
            <?php if (isset($errors['group'])): ?>
                <span class="error"><?= $errors['group'] ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="subject">Предмет:</label>
            <input type="text" id="subject" name="subject" required 
                   value="<?= htmlspecialchars($subject) ?>">
            <?php if (isset($errors['subject'])): ?>
                <span class="error"><?= $errors['subject'] ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="grade">Оцінка (0-100):</label>
            <!-- HTML5 валідація через type="number", min та max -->
            <input type="number" id="grade" name="grade" required min="0" max="100" 
                   value="<?= htmlspecialchars($grade) ?>">
            <?php if (isset($errors['grade'])): ?>
                <span class="error"><?= $errors['grade'] ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Додати оцінку</button>
    </form>

    <script>
        // Крок 5. Додати клієнтську JavaScript-валідацію
        const form = document.getElementById('gradeForm');
        
        form.addEventListener('submit', function(event) {
            const studentName = document.getElementById('studentName').value.trim();
            const grade = document.getElementById('grade').value;

            let hasError = false;

            if (studentName === '') {
                alert('Клієнтська помилка: Ім\'я студента не може бути порожнім.');
                hasError = true;
            } else if (grade === '' || isNaN(grade) || grade < 0 || grade > 100) {
                alert('Клієнтська помилка: Оцінка має бути числом від 0 до 100.');
                hasError = true;
            }

            if (hasError) {
                event.preventDefault(); // Зупиняємо відправку форми
            }
        });

        // Крок 6. Реалізувати сценарій localStorage
        const groupSelect = document.getElementById('group');
        
        // Відновлюємо групу з localStorage, якщо вона там є і форма зараз чиста
        document.addEventListener('DOMContentLoaded', () => {
            const savedGroup = localStorage.getItem('lastSelectedGroup');
            if (savedGroup && !groupSelect.value) {
                groupSelect.value = savedGroup;
            }
        });

        // Запам'ятовуємо вибір при кожній зміні селектора
        groupSelect.addEventListener('change', function() {
            localStorage.setItem('lastSelectedGroup', this.value);
        });
    </script>
</body>
</html>