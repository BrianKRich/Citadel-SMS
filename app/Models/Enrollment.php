<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_course_id',
        'enrollment_date',
        'status',
        'weighted_average',
        'final_letter_grade',
        'grade_points',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'weighted_average' => 'decimal:2',
        'grade_points' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classCourse(): BelongsTo
    {
        return $this->belongsTo(ClassCourse::class, 'class_course_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function scopeEnrolled($query)
    {
        return $query->where('status', 'enrolled');
    }

    public function scopeStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeClassCourse($query, $classCourseId)
    {
        return $query->where('class_course_id', $classCourseId);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function isActive(): bool
    {
        return $this->status === 'enrolled';
    }

    public function calculateFinalGrade(): void
    {
        app(\App\Services\GradeCalculationService::class)->updateEnrollmentGrade($this);
    }
}
