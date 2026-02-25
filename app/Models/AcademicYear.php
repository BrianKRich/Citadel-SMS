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
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', 'current');
    }

    public function setCurrent(): void
    {
        static::query()->update(['status' => 'forming']);
        static::query()->where('id', $this->id)->update(['status' => 'current']);
        $this->status = 'current';
    }
}
