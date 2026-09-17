<?php
class Student {
    public string $name;
    public string $group;
    protected array $grades;

    public function __construct(string $name, string $group, array $grades) {
        $this->name = $name;
        $this->group = $group;
        $this->grades = $grades;
    }

    public function getGrades(): array {
        return $this->grades;
    }

    public function getInfo(): string {
        return "Студент: {$this->name}, Група: {$this->group}";
    }
}