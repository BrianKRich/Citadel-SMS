<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'assessment_id' => Assessment::factory(),
            'score' => fake()->randomFloat(2, 50, 100),
            'notes' => null,
            'is_late' => false,
            'late_penalty' => null,
            'graded_by' => User::factory(),
            'graded_at' => now(),
        ];
    }

    public function late(float $penalty = 10.00): static
    {
        return $this->state(fn () => [
            'is_late' => true,
            'late_penalty' => $penalty,
        ]);
    }
}
