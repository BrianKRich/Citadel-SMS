<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'assessment_id',
        'score',
        'notes',
        'is_late',
        'late_penalty',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'late_penalty' => 'decimal:2',
        'is_late' => 'boolean',
        'graded_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function getAdjustedScoreAttribute(): float
    {
        $score = (float) $this->score;

        if ($this->is_late && $this->late_penalty) {
            $score -= $score * ((float) $this->late_penalty / 100);
        }

        return max(0, $score);
    }
}
