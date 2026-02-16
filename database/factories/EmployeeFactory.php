<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'department_id' => Department::factory(),
            'role_id' => EmployeeRole::factory(),
            'date_of_birth' => fake()->date('Y-m-d', '-25 years'),
            'hire_date' => fake()->date('Y-m-d', 'now'),
            'qualifications' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
