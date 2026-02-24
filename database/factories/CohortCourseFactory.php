<?php

namespace Database\Factories;

use App\Models\ClassModel;
use App\Models\CohortCourse;
use App\Models\Course;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CohortCourse>
 */
class CohortCourseFactory extends Factory
{
    protected $model = CohortCourse::class;

    public function definition(): array
    {
        // ClassModel::factory()->create() triggers boot() which auto-creates alpha+bravo cohorts.
        // We use the auto-created alpha cohort to avoid the unique(class_id, name) constraint.
        $class  = ClassModel::factory()->create();
        $cohort = $class->cohorts()->where('name', 'alpha')->first();

        return [
            'cohort_id'       => $cohort->id,
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
