<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startYear = fake()->numberBetween(2024, 2026);

        return [
            'name' => "{$startYear}-" . ($startYear + 1),
            'start_date' => "{$startYear}-08-01",
            'end_date' => ($startYear + 1) . '-05-31',
            'status' => 'forming',
        ];
    }

    public function current(): static
    {
        return $this->state(fn () => ['status' => 'current']);
    }
}
