<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $dept = fake()->randomElement(['MATH', 'SCI', 'ENG', 'HIST', 'ART']);
        $num = fake()->numberBetween(100, 499);

        return [
            'course_code' => $dept . '-' . $num . '-' . strtoupper(substr(uniqid(), -4)),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'credits' => fake()->randomElement([1.00, 2.00, 3.00, 4.00]),
            'department' => $dept,
            'level' => fake()->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'is_active' => true,
        ];
    }
}
