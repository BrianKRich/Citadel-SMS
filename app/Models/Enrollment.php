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
        'class_id',
        'enrollment_date',
        'status',
        'final_grade',
        'grade_points',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'grade_points' => 'decimal:2',
    ];

    /**
     * Get the student for this enrollment
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class for this enrollment
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get all grades for this enrollment
     * (Future: Phase 3)
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Scope to filter active enrollments
     */
    public function scopeEnrolled($query)
    {
        return $query->where('status', 'enrolled');
    }

    /**
     * Scope to filter by student
     */
    public function scopeStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by class
     */
    public function scopeClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by term (through class relationship)
     */
    public function scopeTerm($query, $termId)
    {
        return $query->whereHas('class', function ($q) use ($termId) {
            $q->where('term_id', $termId);
        });
    }

    /**
     * Check if enrollment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'enrolled';
    }

    /**
     * Calculate final grade (Future: Phase 3)
     * This will aggregate all assessment grades
     */
    public function calculateFinalGrade(): void
    {
        // TODO: Implement in Phase 3 when grades/assessments are added
        // For now, this is a placeholder
    }
}
