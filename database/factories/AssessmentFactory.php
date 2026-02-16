<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        return [
            'class_id' => ClassModel::factory(),
            'assessment_category_id' => AssessmentCategory::factory(),
            'name' => fake()->words(3, true),
            'max_score' => 100.00,
            'due_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'weight' => null,
            'is_extra_credit' => false,
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function extraCredit(): static
    {
        return $this->state(fn () => ['is_extra_credit' => true]);
    }
}
