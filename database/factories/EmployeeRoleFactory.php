<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\EmployeeRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeRole>
 */
class EmployeeRoleFactory extends Factory
{
    protected $model = EmployeeRole::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => fake()->randomElement([
                'Teacher', 'Head of Department', 'Assistant Teacher',
                'Lab Instructor', 'Coordinator',
            ]),
        ];
    }
}
