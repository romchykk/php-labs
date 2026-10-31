-- schema.sql — створення бази та таблиці для варіанту №11
-- Виконайте цей файл у phpMyAdmin або через MySQL-клієнт

CREATE DATABASE IF NOT EXISTS students_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE students_db;

CREATE TABLE IF NOT EXISTS students (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)   NOT NULL,
    group_name    VARCHAR(60)    NOT NULL,
    average_grade DECIMAL(5, 2)  NOT NULL DEFAULT 0.00,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Для вже створеної таблиці: дозволяє зберігати бал 100.00
ALTER TABLE students MODIFY average_grade DECIMAL(5, 2) NOT NULL DEFAULT 0.00;

-- Тестові дані (додаються лише в порожню таблицю, тож повторний запуск безпечний)
INSERT INTO students (name, group_name, average_grade)
SELECT * FROM (
    SELECT 'Олена Мельник'    AS name, 'ІП-31' AS group_name, 94.50 AS average_grade UNION ALL
    SELECT 'Дмитро Коваль',   'ІП-31', 78.00 UNION ALL
    SELECT 'Аліна Шевченко',  'КН-32', 88.75 UNION ALL
    SELECT 'Богдан Лисенко',  'КН-32', 65.20 UNION ALL
    SELECT 'Марія Бондаренко','ІБ-33', 91.00 UNION ALL
    SELECT 'Тарас Гриценко',  'ІБ-33', 55.80
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM students);
