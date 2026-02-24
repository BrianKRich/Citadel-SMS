<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'cohort_course_id',
        'date',
        'status',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function cohortCourse(): BelongsTo
    {
        return $this->belongsTo(CohortCourse::class, 'cohort_course_id');
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForCohortCourse($query, $cohortCourseId)
    {
        return $query->where('cohort_course_id', $cohortCourseId);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
