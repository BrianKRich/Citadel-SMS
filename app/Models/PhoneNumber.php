<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PhoneNumber extends Model
{
    protected $fillable = ['area_code', 'number', 'type', 'label', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function phoneable(): MorphTo
    {
        return $this->morphTo();
    }
}
