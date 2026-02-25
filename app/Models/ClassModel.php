<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'academic_year_id',
        'name',
        'class_number',
        'ngb_number',
        'status',
        'start_date',
        'end_date',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classCourses(): HasMany
    {
        return $this->hasMany(ClassCourse::class, 'class_id');
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
