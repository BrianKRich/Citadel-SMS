<?php

namespace Database\Factories;

use App\Models\Cohort;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cohort>
 *
 * NOTE: Cohorts are auto-created by ClassModel::boot() when a ClassModel is created.
 * This factory does NOT insert a new record; it returns an existing auto-created cohort
 * by creating a ClassModel and retrieving the alpha cohort from it.
 */
class CohortFactory extends Factory
{
    protected $model = Cohort::class;

    public function definition(): array
    {
        // ClassModel::factory()->create() triggers boot() which auto-creates alpha+bravo.
        // We return the auto-created alpha cohort's attributes to avoid unique constraint.
        $class  = ClassModel::factory()->create();
        $cohort = $class->cohorts()->where('name', 'alpha')->firstOrFail();

        return [
            'class_id'   => $cohort->class_id,
            'name'       => $cohort->name,
            'start_date' => $cohort->start_date,
            'end_date'   => $cohort->end_date,
        ];
    }

    /**
     * Override create() to return the existing auto-created cohort instead of inserting.
     * This avoids violating the unique(class_id, name) constraint.
     */
    public function create($attributes = [], ?\Illuminate\Database\Eloquent\Model $parent = null): Cohort
    {
        $class = ClassModel::factory()->create();
        return $class->cohorts()->where('name', 'alpha')->firstOrFail();
    }
}
