<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Http\Request;

trait SavesCustomFieldValues
{
    private function saveCustomFieldValues(Request $request, string $entityType, int $entityId): void
    {
        $values = $request->input('custom_field_values', []);

        foreach ($values as $fieldId => $value) {
            $field = CustomField::find((int) $fieldId);

            if (!$field || $field->entity_type !== $entityType || !$field->is_active) {
                continue;
            }

            CustomFieldValue::updateOrCreate(
                [
                    'custom_field_id' => $field->id,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                ],
                [
                    'value' => is_array($value) ? implode(',', $value) : (string) $value,
                ]
            );
        }
    }
}
