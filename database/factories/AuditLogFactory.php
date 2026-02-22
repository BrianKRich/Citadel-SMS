<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'auditable_type' => Student::class,
            'auditable_id'   => $this->faker->numberBetween(1, 100),
            'subject_label'  => 'STU-2026-001 (Jane Smith)',
            'action'         => $this->faker->randomElement(['created', 'updated', 'deleted', 'restored']),
            'old_values'     => null,
            'new_values'     => null,
        ];
    }

    public function created(): static
    {
        return $this->state(['action' => 'created', 'old_values' => null]);
    }

    public function updated(): static
    {
        return $this->state([
            'action'     => 'updated',
            'old_values' => ['status' => 'active'],
            'new_values' => ['status' => 'inactive'],
        ]);
    }

    public function deleted(): static
    {
        return $this->state(['action' => 'deleted', 'new_values' => null]);
    }

    public function restored(): static
    {
        return $this->state(['action' => 'restored', 'old_values' => null, 'new_values' => null]);
    }
}
