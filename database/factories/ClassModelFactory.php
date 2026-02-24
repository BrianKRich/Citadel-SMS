<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassModel>
 */
class ClassModelFactory extends Factory
{
    protected $model = ClassModel::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'academic_year_id' => AcademicYear::factory(),
            'class_number' => (string) (40 + $counter),
            'ngb_number' => 'NGB-' . fake()->unique()->numerify('####'),
            'status' => 'forming',
        ];
    }
}
