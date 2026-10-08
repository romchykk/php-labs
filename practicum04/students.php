<?php
// students.php — функції для роботи з таблицею students

// ---------- SELECT ----------

/**
 * Повертає всіх студентів.
 */
function getAllStudents(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM students ORDER BY id')->fetchAll();
}

/**
 * Вибірка: findByGroup($group)
 * SELECT * FROM students WHERE group_name = :group
 */
function findByGroup(PDO $pdo, string $group): array
{
    $stmt = $pdo->prepare('SELECT * FROM students WHERE group_name = :group ORDER BY name');
    $stmt->execute([':group' => $group]);
    return $stmt->fetchAll();
}

/**
 * Вибірка: topStudent()
 * SELECT * FROM students ORDER BY average_grade DESC LIMIT 1
 */
function topStudent(PDO $pdo): array|false
{
    return $pdo->query('SELECT * FROM students ORDER BY average_grade DESC LIMIT 1')->fetch();
}

/**
 * Отримати одного студента за id (для форми редагування).
 */
function getStudentById(PDO $pdo, int $id): array|false
{
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

// ---------- INSERT ----------

/**
 * Зміна даних: addStudent()
 * INSERT INTO students (name, group_name, average_grade) VALUES (...)
 */
function addStudent(PDO $pdo, string $name, string $group_name, float $average_grade): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO students (name, group_name, average_grade) VALUES (:name, :group_name, :average_grade)'
    );
    $stmt->execute([
        ':name'          => $name,
        ':group_name'    => $group_name,
        ':average_grade' => $average_grade,
    ]);
    return (int) $pdo->lastInsertId();
}

// ---------- UPDATE ----------

/**
 * Зміна даних: updateStudent($id, ...)
 * UPDATE students SET average_grade = :grade WHERE id = :id
 * (також оновлюємо ім'я та групу для повноти)
 */
function updateStudent(PDO $pdo, int $id, string $name, string $group_name, float $average_grade): int
{
    $stmt = $pdo->prepare(
        'UPDATE students SET name = :name, group_name = :group_name, average_grade = :grade WHERE id = :id'
    );
    $stmt->execute([
        ':name'       => $name,
        ':group_name' => $group_name,
        ':grade'      => $average_grade,
        ':id'         => $id,
    ]);
    return $stmt->rowCount();
}

// ---------- DELETE ----------

/**
 * Зміна даних: deleteStudent($id)
 * DELETE FROM students WHERE id = :id
 */
function deleteStudent(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare('DELETE FROM students WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount();
}
