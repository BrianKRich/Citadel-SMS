<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\ClassCourse;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        return [
            'student_id'       => Student::factory(),
            'class_course_id'  => ClassCourse::factory(),
            'date'             => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status'           => fake()->randomElement(['present', 'absent', 'late', 'excused']),
            'notes'            => null,
            'marked_by'        => User::factory(),
        ];
    }

    public function present(): static
    {
        return $this->state(fn () => ['status' => 'present']);
    }

    public function absent(): static
    {
        return $this->state(fn () => ['status' => 'absent']);
    }

    public function late(): static
    {
        return $this->state(fn () => ['status' => 'late']);
    }

    public function excused(): static
    {
        return $this->state(fn () => ['status' => 'excused']);
    }
}
