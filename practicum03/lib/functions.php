<?php
// Функція для розрахунку середнього бала
function calcAverage(array $grades): float {
    if (empty($grades)) return 0.0;
    $sum = array_sum($grades);
    return round($sum / count($grades), 2);
}

// Функція для переведення оцінки в літерний формат (A-F)
function gradeToLetter(float $average): string {
    if ($average >= 95) return 'A';
    if ($average >= 85) return 'B';
    if ($average >= 75) return 'C';
    if ($average >= 65) return 'D';
    if ($average >= 60) return 'E';
    return 'F';
}