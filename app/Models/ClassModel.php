<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'academic_year_id',
        'class_number',
        'ngb_number',
        'status',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Auto-create Cohort Alpha and Cohort Bravo when a class is created
        static::created(function (ClassModel $class) {
            $class->cohorts()->createMany([
                ['name' => 'alpha'],
                ['name' => 'bravo'],
            ]);
        });
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class, 'class_id');
    }

    public function cohortCourses(): HasManyThrough
    {
        return $this->hasManyThrough(CohortCourse::class, Cohort::class, 'class_id', 'cohort_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('class_number', 'like', "%{$search}%")
            ->orWhere('ngb_number', 'like', "%{$search}%");
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
