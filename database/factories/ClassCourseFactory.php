<?php

namespace Database\Factories;

use App\Models\ClassCourse;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassCourse>
 */
class ClassCourseFactory extends Factory
{
    protected $model = ClassCourse::class;

    public function definition(): array
    {
        return [
            'class_id'        => ClassModel::factory(),
            'course_id'       => Course::factory(),
            'instructor_type' => 'staff',
            'employee_id'     => Employee::factory(),
            'institution_id'  => null,
            'room'            => 'Room ' . fake()->numberBetween(100, 400),
            'schedule'        => [
                ['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'],
                ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00'],
            ],
            'max_students'    => 30,
            'status'          => 'open',
        ];
    }
}
