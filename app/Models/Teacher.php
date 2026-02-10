<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'hire_date',
        'department',
        'specialization',
        'qualifications',
        'photo',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
    ];

    /**
     * Boot the model and generate teacher_id
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($teacher) {
            if (empty($teacher->teacher_id)) {
                $teacher->teacher_id = static::generateTeacherId();
            }
        });
    }

    /**
     * Generate unique teacher ID (e.g., TCH-2026-001)
     */
    public static function generateTeacherId(): string
    {
        $year = date('Y');
        $lastTeacher = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastTeacher ? intval(substr($lastTeacher->teacher_id, -3)) + 1 : 1;

        return sprintf('TCH-%s-%03d', $year, $number);
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user associated with the teacher
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the classes taught by the teacher
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class);
    }

    /**
     * Scope to filter active teachers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by department
     */
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope to search teachers by name or teacher ID
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('teacher_id', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
