<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'uploaded_by',
        'uuid',
        'original_name',
        'stored_path',
        'mime_type',
        'size_bytes',
        'category',
        'description',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'entity_id'  => 'integer',
    ];

    protected $appends = ['formatted_size'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Document $doc) {
            if (empty($doc->uuid)) {
                $doc->uuid = (string) Str::uuid();
            }
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size_bytes;

        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 1) . ' MB';
        }

        if ($bytes >= 1_024) {
            return round($bytes / 1_024) . ' KB';
        }

        return $bytes . ' B';
    }
}
