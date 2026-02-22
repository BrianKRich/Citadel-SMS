<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CustomFieldController extends Controller
{
    public function index()
    {
        $fields = CustomField::orderBy('entity_type')->orderBy('sort_order')->orderBy('label')->get();

        return Inertia::render('Admin/CustomFields/Index', [
            'fields' => $fields,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/CustomFields/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'string', 'in:Student,Employee,Course,Class,Enrollment'],
            'label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', 'string', 'in:text,textarea,number,date,boolean,select'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        // Validate options are provided for select fields
        if ($validated['field_type'] === 'select' && empty($validated['options'])) {
            return back()->withErrors(['options' => 'At least one option is required for select fields.'])->withInput();
        }

        $validated['name'] = $this->generateUniqueName($validated['entity_type'], $validated['label']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Clear options for non-select fields
        if ($validated['field_type'] !== 'select') {
            $validated['options'] = null;
        }

        CustomField::create($validated);

        return redirect()->route('admin.custom-fields.index')
            ->with('success', "Custom field \"{$validated['label']}\" created successfully.");
    }

    public function edit(CustomField $customField)
    {
        return Inertia::render('Admin/CustomFields/Edit', [
            'field' => $customField,
        ]);
    }

    public function update(Request $request, CustomField $customField)
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'string', 'in:Student,Employee,Course,Class,Enrollment'],
            'label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', 'string', 'in:text,textarea,number,date,boolean,select'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        // Validate options are provided for select fields
        if ($validated['field_type'] === 'select' && empty($validated['options'])) {
            return back()->withErrors(['options' => 'At least one option is required for select fields.'])->withInput();
        }

        // Regenerate name only if label changed and new name is free
        $newName = Str::slug($validated['label'], '_');
        if ($newName !== $customField->name) {
            $validated['name'] = $this->generateUniqueName($validated['entity_type'], $validated['label'], $customField->id);
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($validated['field_type'] !== 'select') {
            $validated['options'] = null;
        }

        $customField->update($validated);

        return redirect()->route('admin.custom-fields.index')
            ->with('success', "Custom field \"{$customField->label}\" updated successfully.");
    }

    public function destroy(CustomField $customField)
    {
        $label = $customField->label;
        $valueCount = $customField->values()->count();
        $customField->delete();

        return redirect()->route('admin.custom-fields.index')
            ->with('success', "\"{$label}\" field and {$valueCount} value(s) deleted.");
    }

    public function toggle(CustomField $customField)
    {
        $customField->update(['is_active' => !$customField->is_active]);

        return back();
    }

    private function generateUniqueName(string $entityType, string $label, ?int $excludeId = null): string
    {
        $base = Str::slug($label, '_');
        $name = $base;
        $suffix = 2;

        while (true) {
            $exists = CustomField::where('entity_type', $entityType)
                ->where('name', $name)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();

            if (!$exists) {
                break;
            }

            $name = $base . '_' . $suffix;
            $suffix++;
        }

        return $name;
    }
}
