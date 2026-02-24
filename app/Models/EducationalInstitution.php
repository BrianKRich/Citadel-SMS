<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalInstitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'address',
        'phone',
        'contact_person',
    ];

    public function cohortCourses(): HasMany
    {
        return $this->hasMany(CohortCourse::class, 'institution_id');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
