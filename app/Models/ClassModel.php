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
        'course_id',
        'teacher_id',
        'academic_year_id',
        'term_id',
        'section_name',
        'room',
        'schedule',
        'max_students',
        'status',
    ];

    protected $casts = [
        'schedule' => 'array',
        'max_students' => 'integer',
    ];

    /**
     * Get the course for this class
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the teacher for this class
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the academic year for this class
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the term for this class
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get all enrollments for this class
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    /**
     * Get enrolled students through enrollments
     */
    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            Enrollment::class,
            'class_id',
            'id',
            'id',
            'student_id'
        );
    }

    /**
     * Get the count of enrolled students
     */
    public function getEnrolledCountAttribute(): int
    {
        return $this->enrollments()->where('status', 'enrolled')->count();
    }

    /**
     * Check if class is full
     */
    public function isFull(): bool
    {
        return $this->enrolled_count >= $this->max_students;
    }

    /**
     * Get available seats
     */
    public function getAvailableSeatsAttribute(): int
    {
        return max(0, $this->max_students - $this->enrolled_count);
    }

    /**
     * Scope to filter by term
     */
    public function scopeTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Scope to filter by course
     */
    public function scopeCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope to filter by teacher
     */
    public function scopeTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get open classes
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope to get classes with available seats
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'open')
            ->whereRaw("(SELECT COUNT(*) FROM enrollments WHERE class_id = classes.id AND status = 'enrolled') < max_students");
    }

    /**
     * Scope to search classes
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('course', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('course_code', 'like', "%{$search}%");
        })->orWhereHas('teacher', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
        })->orWhere('section_name', 'like', "%{$search}%")
          ->orWhere('room', 'like', "%{$search}%");
    }
}
