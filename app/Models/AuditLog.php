<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'subject_label',
        'action',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAuditableTypeShortAttribute(): string
    {
        return class_basename($this->auditable_type);
    }

    public function scopeModelType($query, string $type): mixed
    {
        $map = [
            'Student'    => \App\Models\Student::class,
            'Employee'   => \App\Models\Employee::class,
            'Grade'      => \App\Models\Grade::class,
            'Enrollment' => \App\Models\Enrollment::class,
        ];

        return $query->where('auditable_type', $map[$type] ?? $type);
    }

    public function scopeActor($query, int $userId): mixed
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAction($query, string $action): mixed
    {
        return $query->where('action', $action);
    }

    public function scopeDateFrom($query, string $date): mixed
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    public function scopeDateTo($query, string $date): mixed
    {
        return $query->whereDate('created_at', '<=', $date);
    }
}
