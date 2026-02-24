<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'training_course_id',
        'date_completed',
        'trainer_name',
        'notes',
    ];

    protected $casts = [
        'date_completed' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function trainingCourse(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('trainer_name', 'like', "%{$search}%")
              ->orWhereHas('trainingCourse', fn ($c) => $c->where('name', 'like', "%{$search}%"))
              ->orWhereHas('employee', fn ($e) => $e->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%"));
        });
    }
}
