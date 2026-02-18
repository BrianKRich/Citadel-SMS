<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Get the academic year for the term
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the classes for the term
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class);
    }

    /**
     * Scope to get the current term
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Set this term as current and unset others within the same academic year
     */
    public function setCurrent()
    {
        static::where('academic_year_id', $this->academic_year_id)
            ->update(['is_current' => false]);
        static::where('id', $this->id)->update(['is_current' => true]);
        $this->is_current = true;
    }
}
