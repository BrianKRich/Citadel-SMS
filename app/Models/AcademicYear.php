<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function setCurrent(): void
    {
        static::query()->update(['is_current' => false]);
        static::query()->where('id', $this->id)->update(['is_current' => true]);
        $this->is_current = true;
    }
}
