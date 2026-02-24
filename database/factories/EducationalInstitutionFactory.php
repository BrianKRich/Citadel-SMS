<?php

namespace Database\Factories;

use App\Models\EducationalInstitution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationalInstitution>
 */
class EducationalInstitutionFactory extends Factory
{
    protected $model = EducationalInstitution::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' ' . fake()->randomElement(['College', 'University', 'Technical College']),
            'type' => fake()->randomElement(['technical_college', 'university']),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'contact_person' => fake()->name(),
        ];
    }
}
