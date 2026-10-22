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
    average_grade DECIMAL(4, 2)  NOT NULL DEFAULT 0.00,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Тестові дані
INSERT INTO students (name, group_name, average_grade) VALUES
    ('Олена Мельник',   'ІП-31', 94.50),
    ('Дмитро Коваль',   'ІП-31', 78.00),
    ('Аліна Шевченко',  'КН-32', 88.75),
    ('Богдан Лисенко',  'КН-32', 65.20),
    ('Марія Бондаренко','ІБ-33', 91.00),
    ('Тарас Гриценко',  'ІБ-33', 55.80);
