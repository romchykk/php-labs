<?php
require_once 'Student.php';
require_once __DIR__ . '/../lib/functions.php';

class Gradebook {
    private array $students = [];

    public function addStudent(Student $student): void {
        $this->students[] = $student;
    }

    public function findByGroup(string $group): array {
        $result = [];
        foreach ($this->students as $student) {
            if ($student->group === $group) {
                $result[] = $student;
            }
        }
        return $result;
    }

    public function topStudent(): ?Student {
        if (empty($this->students)) return null;

        $top = $this->students[0];
        $maxAvg = calcAverage($top->getGrades());

        foreach ($this->students as $student) {
            $avg = calcAverage($student->getGrades());
            if ($avg > $maxAvg) {
                $maxAvg = $avg;
                $top = $student;
            }
        }
        return $top;
    }

    public function getAll(): array {
        return $this->students;
    }
}