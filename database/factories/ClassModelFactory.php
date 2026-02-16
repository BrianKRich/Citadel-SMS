<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassModel>
 */
class ClassModelFactory extends Factory
{
    protected $model = ClassModel::class;

    public function definition(): array
    {
        $academicYear = AcademicYear::factory();

        return [
            'course_id' => Course::factory(),
            'employee_id' => Employee::factory(),
            'academic_year_id' => $academicYear,
            'term_id' => Term::factory()->for($academicYear, 'academicYear'),
            'section_name' => fake()->randomElement(['A', 'B', 'C', 'Morning', 'Afternoon']),
            'room' => 'Room ' . fake()->numberBetween(100, 400),
            'schedule' => [
                ['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'],
                ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00'],
            ],
            'max_students' => 30,
            'status' => 'open',
        ];
    }
}
