<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'relationship',
        'email',
        'address',
        'occupation',
        'user_id',
    ];

    public function phoneNumbers(): MorphMany
    {
        return $this->morphMany(PhoneNumber::class, 'phoneable');
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user associated with the guardian (for portal access)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the students for the guardian
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get the primary students for the guardian
     */
    public function primaryStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('is_primary', true);
    }
}
