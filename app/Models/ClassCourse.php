<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassCourse extends Model
{
    use HasFactory;

    protected $table = 'class_courses';

    protected $fillable = [
        'class_id',
        'course_id',
        'instructor_type',
        'employee_id',
        'institution_id',
        'room',
        'schedule',
        'max_students',
        'status',
    ];

    protected $casts = [
        'schedule' => 'array',
        'max_students' => 'integer',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(EducationalInstitution::class, 'institution_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_course_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'class_course_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'class_course_id');
    }

    public function getEnrolledCountAttribute(): int
    {
        return $this->enrollments()->where('status', 'enrolled')->count();
    }

    public function getAvailableSeatsAttribute(): int
    {
        return max(0, $this->max_students - $this->enrolled_count);
    }

    public function isFull(): bool
    {
        return $this->enrolled_count >= $this->max_students;
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereHas('course', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('course_code', 'like', "%{$search}%");
        })->orWhereHas('employee', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%");
        })->orWhere('room', 'like', "%{$search}%");
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'open')
            ->whereRaw("(SELECT COUNT(*) FROM enrollments WHERE class_course_id = class_courses.id AND status = 'enrolled') < max_students");
    }
}
