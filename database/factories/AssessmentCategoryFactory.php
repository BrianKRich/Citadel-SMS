<?php

namespace Database\Factories;

use App\Models\AssessmentCategory;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssessmentCategory>
 */
class AssessmentCategoryFactory extends Factory
{
    protected $model = AssessmentCategory::class;

    public function definition(): array
    {
        return [
            'course_id' => null,
            'name' => fake()->randomElement(['Homework', 'Quizzes', 'Midterm', 'Final Exam', 'Projects']),
            'weight' => fake()->randomElement([0.15, 0.20, 0.25, 0.30]),
            'description' => fake()->sentence(),
        ];
    }

    public function forCourse(): static
    {
        return $this->state(fn () => [
            'course_id' => Course::factory(),
        ]);
    }
}
