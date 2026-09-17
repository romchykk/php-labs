<?php
require_once 'Student.php';
require_once __DIR__ . '/../lib/functions.php';

class HonorsStudent extends Student {
    private float $scholarship;

    public function __construct(string $name, string $group, array $grades, float $scholarship) {
        parent::__construct($name, $group, $grades); // Виклик конструктора батьківського класу
        $this->scholarship = $scholarship;
    }

    public function getInfo(): string {
        $baseInfo = parent::getInfo(); // Виклик методу батьківського класу
        $average = calcAverage($this->grades);
        return $baseInfo . " | Середній бал: {$average} | Стипендія: {$this->scholarship} грн";
    }
}