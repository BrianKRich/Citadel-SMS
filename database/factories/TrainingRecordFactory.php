<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\TrainingCourse;
use App\Models\TrainingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingRecordFactory extends Factory
{
    protected $model = TrainingRecord::class;

    public function definition(): array
    {
        return [
            'employee_id'        => Employee::factory(),
            'training_course_id' => TrainingCourse::factory(),
            'date_completed'     => $this->faker->date(),
            'trainer_name'       => $this->faker->name(),
            'notes'              => $this->faker->optional()->sentence(),
        ];
    }
}
