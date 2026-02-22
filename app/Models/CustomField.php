<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'label',
        'name',
        'field_type',
        'options',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function scopeForEntity($query, string $entityType): mixed
    {
        return $query->where('entity_type', $entityType);
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }

    /**
     * Returns all fields for an entity type, each with its value for the given record pre-loaded.
     * Only active fields are returned.
     */
    public static function forEntityWithValues(string $entityType, int $entityId): Collection
    {
        $fields = static::forEntity($entityType)
            ->active()
            ->orderBy('sort_order')
            ->get();

        $values = CustomFieldValue::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->whereIn('custom_field_id', $fields->pluck('id'))
            ->get()
            ->keyBy('custom_field_id');

        return $fields->map(function ($field) use ($values) {
            $field->pivot_value = $values->get($field->id)?->value;
            return $field;
        });
    }
}
