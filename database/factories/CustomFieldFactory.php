<?php

namespace Database\Factories;

use App\Models\CustomField;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        return [
            'entity_type' => $this->faker->randomElement(['Student', 'Employee', 'Course', 'Class', 'Enrollment']),
            'label' => $this->faker->unique()->words(2, true),
            'name' => $this->faker->unique()->word(),
            'field_type' => $this->faker->randomElement(['text', 'textarea', 'number', 'date', 'boolean']),
            'options' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
