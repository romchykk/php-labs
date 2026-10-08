-- Крок 1: Створити базу даних і таблицю
-- Виконати один раз у phpMyAdmin або консолі: mysql -u root -p < create_table.sql

CREATE DATABASE IF NOT EXISTS practicum4
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE practicum4;

CREATE TABLE IF NOT EXISTS students (
    id            INT          AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    group_name    VARCHAR(50)  NOT NULL,
    average_grade DECIMAL(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

