<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'name',
        'description',
        'credits',
        'department',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credits' => 'decimal:2',
    ];

    public function classCourses(): HasMany
    {
        return $this->hasMany(ClassCourse::class);
    }

    public function assessmentCategories(): HasMany
    {
        return $this->hasMany(AssessmentCategory::class);
    }

    /**
     * Scope to filter active courses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by department
     */
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope to filter by level
     */
    public function scopeLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to search courses by code or name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('course_code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
