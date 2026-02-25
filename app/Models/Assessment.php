<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_course_id',
        'assessment_category_id',
        'name',
        'max_score',
        'due_date',
        'weight',
        'is_extra_credit',
        'status',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'due_date' => 'date',
        'is_extra_credit' => 'boolean',
    ];

    public function classCourse(): BelongsTo
    {
        return $this->belongsTo(ClassCourse::class, 'class_course_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'assessment_category_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function getEffectiveWeightAttribute(): float
    {
        return (float) ($this->weight ?? $this->category->weight);
    }

    public function scopeClassCourse($query, $classCourseId)
    {
        return $query->where('class_course_id', $classCourseId);
    }

    public function scopeCategory($query, $categoryId)
    {
        return $query->where('assessment_category_id', $categoryId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
