<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_default',
        'scale',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'scale' => 'array',
    ];

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getLetterGrade(float $percentage): string
    {
        $scale = collect($this->scale)->sortByDesc('min_percentage');

        foreach ($scale as $entry) {
            if ($percentage >= $entry['min_percentage']) {
                return $entry['letter'];
            }
        }

        return $scale->last()['letter'] ?? 'F';
    }

    public function getGpaPoints(string $letter): float
    {
        $entry = collect($this->scale)->firstWhere('letter', $letter);

        return $entry ? (float) $entry['gpa_points'] : 0.0;
    }

    public function setDefault(): void
    {
        static::query()->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}
