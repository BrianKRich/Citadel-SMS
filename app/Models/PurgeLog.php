<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurgeLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'older_than',
        'purged_count',
        'reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function olderThanLabel(): string
    {
        return match ($this->older_than) {
            '30'  => 'Older than 30 days',
            '90'  => 'Older than 90 days',
            '180' => 'Older than 180 days',
            '365' => 'Older than 1 year',
            'all' => 'All records',
            default => $this->older_than,
        };
    }
}
