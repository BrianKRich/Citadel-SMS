<?php

namespace Database\Factories;

use App\Models\ClassCourse;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_course_id' => ClassCourse::factory(),
            'enrollment_date' => fake()->date('Y-m-d', 'now'),
            'status' => 'enrolled',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function withGrade(string $grade, float $points): static
    {
        return $this->state(fn () => [
            'final_letter_grade' => $grade,
            'grade_points' => $points,
            'status' => 'completed',
        ]);
    }
}
