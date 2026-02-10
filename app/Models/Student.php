<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'email',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'emergency_contact_name',
        'enrollment_date',
        'status',
        'photo',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
    ];

    /**
     * Boot the model and generate student_id
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->student_id)) {
                $student->student_id = static::generateStudentId();
            }
        });
    }

    /**
     * Generate unique student ID (e.g., STU-2026-001)
     */
    public static function generateStudentId(): string
    {
        $year = date('Y');
        $lastStudent = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastStudent ? intval(substr($lastStudent->student_id, -3)) + 1 : 1;

        return sprintf('STU-%s-%03d', $year, $number);
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function phoneNumbers(): MorphMany
    {
        return $this->morphMany(PhoneNumber::class, 'phoneable');
    }

    /**
     * Get the user associated with the student (for portal access)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the guardians for the student
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get the primary guardian for the student
     */
    public function primaryGuardian(): BelongsToMany
    {
        return $this->guardians()->wherePivot('is_primary', true);
    }

    /**
     * Get the enrollments for the student
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the attendance records for the student
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Scope to filter active students
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to search students by name or student ID
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('student_id', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
