<?php

namespace Tests\Feature\Admin;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomFieldTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // -----------------------------------------------------------------------
    // Auth guards
    // -----------------------------------------------------------------------

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.custom-fields.index'))->assertRedirect(route('login'));
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('admin.custom-fields.store'), [])->assertRedirect(route('login'));
    }

    public function test_update_requires_auth(): void
    {
        $field = CustomField::factory()->create();
        $this->put(route('admin.custom-fields.update', $field), [])->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $field = CustomField::factory()->create();
        $this->delete(route('admin.custom-fields.destroy', $field))->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // CRUD
    // -----------------------------------------------------------------------

    public function test_index_renders_with_no_fields(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.custom-fields.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/CustomFields/Index')
                ->has('fields', 0)
            );
    }

    public function test_index_lists_all_fields(): void
    {
        CustomField::factory()->count(3)->create(['entity_type' => 'Student']);
        CustomField::factory()->count(2)->create(['entity_type' => 'Employee']);

        $this->actingAs($this->admin())
            ->get(route('admin.custom-fields.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/CustomFields/Index')
                ->has('fields', 5)
            );
    }

    public function test_create_field(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'entity_type' => 'Student',
                'label' => 'Has IEP',
                'field_type' => 'boolean',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.custom-fields.index'));

        $this->assertDatabaseHas('custom_fields', [
            'entity_type' => 'Student',
            'label' => 'Has IEP',
            'name' => 'has_iep',
            'field_type' => 'boolean',
            'is_active' => true,
        ]);
    }

    public function test_create_select_field_with_options(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'entity_type' => 'Student',
                'label' => 'Transportation',
                'field_type' => 'select',
                'options' => ['Bus', 'Car', 'Walk'],
            ])
            ->assertRedirect(route('admin.custom-fields.index'));

        $field = CustomField::where('name', 'transportation')->first();
        $this->assertNotNull($field);
        $this->assertEquals(['Bus', 'Car', 'Walk'], $field->options);
    }

    public function test_update_field(): void
    {
        $field = CustomField::factory()->create([
            'entity_type' => 'Student',
            'label' => 'Old Label',
            'field_type' => 'text',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.custom-fields.update', $field), [
                'entity_type' => 'Student',
                'label' => 'New Label',
                'field_type' => 'text',
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.custom-fields.index'));

        $this->assertDatabaseHas('custom_fields', [
            'id' => $field->id,
            'label' => 'New Label',
            'name' => 'new_label',
        ]);
    }

    public function test_destroy_field_and_cascades_values(): void
    {
        $field = CustomField::factory()->create(['entity_type' => 'Student']);
        CustomFieldValue::create([
            'custom_field_id' => $field->id,
            'entity_type' => 'Student',
            'entity_id' => 1,
            'value' => 'test',
        ]);

        $this->assertDatabaseCount('custom_field_values', 1);

        $this->actingAs($this->admin())
            ->delete(route('admin.custom-fields.destroy', $field))
            ->assertRedirect(route('admin.custom-fields.index'));

        $this->assertDatabaseMissing('custom_fields', ['id' => $field->id]);
        $this->assertDatabaseCount('custom_field_values', 0);
    }

    // -----------------------------------------------------------------------
    // Toggle
    // -----------------------------------------------------------------------

    public function test_toggle_deactivates_active_field(): void
    {
        $field = CustomField::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.toggle', $field))
            ->assertRedirect();

        $this->assertDatabaseHas('custom_fields', ['id' => $field->id, 'is_active' => false]);
    }

    public function test_toggle_activates_inactive_field(): void
    {
        $field = CustomField::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.toggle', $field))
            ->assertRedirect();

        $this->assertDatabaseHas('custom_fields', ['id' => $field->id, 'is_active' => true]);
    }

    // -----------------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------------

    public function test_store_requires_entity_type(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'label' => 'Test',
                'field_type' => 'text',
            ])
            ->assertSessionHasErrors('entity_type');
    }

    public function test_store_requires_label(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'entity_type' => 'Student',
                'field_type' => 'text',
            ])
            ->assertSessionHasErrors('label');
    }

    public function test_store_requires_options_for_select_type(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'entity_type' => 'Student',
                'label' => 'Test',
                'field_type' => 'select',
                'options' => [],
            ])
            ->assertSessionHasErrors('options');
    }

    public function test_store_rejects_invalid_entity_type(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'entity_type' => 'InvalidModel',
                'label' => 'Test',
                'field_type' => 'text',
            ])
            ->assertSessionHasErrors('entity_type');
    }

    // -----------------------------------------------------------------------
    // Name generation
    // -----------------------------------------------------------------------

    public function test_name_generated_from_label(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'entity_type' => 'Student',
                'label' => 'Bus Number',
                'field_type' => 'text',
            ]);

        $this->assertDatabaseHas('custom_fields', ['name' => 'bus_number']);
    }

    public function test_name_collision_appends_suffix(): void
    {
        // Create first field with name 'has_iep'
        CustomField::factory()->create([
            'entity_type' => 'Student',
            'name' => 'has_iep',
            'label' => 'Has IEP',
            'field_type' => 'boolean',
        ]);

        // Create second field with same label
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'entity_type' => 'Student',
                'label' => 'Has IEP',
                'field_type' => 'text',
            ]);

        $this->assertDatabaseHas('custom_fields', ['name' => 'has_iep_2']);
    }

    public function test_name_unique_per_entity_type_not_globally(): void
    {
        // 'notes' field for Student
        CustomField::factory()->create([
            'entity_type' => 'Student',
            'name' => 'notes',
            'label' => 'Notes',
            'field_type' => 'textarea',
        ]);

        // 'notes' field for Employee should be allowed (different entity)
        $this->actingAs($this->admin())
            ->post(route('admin.custom-fields.store'), [
                'entity_type' => 'Employee',
                'label' => 'Notes',
                'field_type' => 'textarea',
            ])
            ->assertRedirect(route('admin.custom-fields.index'));

        $this->assertDatabaseHas('custom_fields', ['entity_type' => 'Employee', 'name' => 'notes']);
    }

    // -----------------------------------------------------------------------
    // Value saving
    // -----------------------------------------------------------------------

    public function test_storing_student_saves_custom_field_values(): void
    {
        $field = CustomField::factory()->create([
            'entity_type' => 'Student',
            'field_type' => 'text',
            'is_active' => true,
        ]);

        $student = Student::factory()->create();

        // Simulate saving custom values on update
        $this->actingAs($this->admin())
            ->patch(route('admin.students.update', $student), array_merge(
                $this->validStudentData($student),
                ['custom_field_values' => [$field->id => 'IEP on file']]
            ));

        $this->assertDatabaseHas('custom_field_values', [
            'custom_field_id' => $field->id,
            'entity_type' => 'Student',
            'entity_id' => $student->id,
            'value' => 'IEP on file',
        ]);
    }

    public function test_updating_student_overwrites_custom_field_values(): void
    {
        $field = CustomField::factory()->create([
            'entity_type' => 'Student',
            'field_type' => 'text',
            'is_active' => true,
        ]);

        $student = Student::factory()->create();

        CustomFieldValue::create([
            'custom_field_id' => $field->id,
            'entity_type' => 'Student',
            'entity_id' => $student->id,
            'value' => 'old value',
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.students.update', $student), array_merge(
                $this->validStudentData($student),
                ['custom_field_values' => [$field->id => 'new value']]
            ));

        $this->assertDatabaseHas('custom_field_values', [
            'custom_field_id' => $field->id,
            'entity_id' => $student->id,
            'value' => 'new value',
        ]);
        $this->assertDatabaseCount('custom_field_values', 1);
    }

    // -----------------------------------------------------------------------
    // Show page
    // -----------------------------------------------------------------------

    public function test_student_show_includes_custom_field_values(): void
    {
        $field = CustomField::factory()->create([
            'entity_type' => 'Student',
            'is_active' => true,
        ]);

        $student = Student::factory()->create();

        CustomFieldValue::create([
            'custom_field_id' => $field->id,
            'entity_type' => 'Student',
            'entity_id' => $student->id,
            'value' => 'Yes',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.students.show', $student))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Students/Show')
                ->has('customFields', 1)
                ->where('customFields.0.pivot_value', 'Yes')
            );
    }

    // -----------------------------------------------------------------------
    // Inactive field values not saved
    // -----------------------------------------------------------------------

    public function test_inactive_field_values_not_saved(): void
    {
        $field = CustomField::factory()->create([
            'entity_type' => 'Student',
            'field_type' => 'text',
            'is_active' => false,
        ]);

        $student = Student::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.students.update', $student), array_merge(
                $this->validStudentData($student),
                ['custom_field_values' => [$field->id => 'should not save']]
            ));

        $this->assertDatabaseMissing('custom_field_values', [
            'custom_field_id' => $field->id,
            'entity_id' => $student->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function validStudentData(Student $student): array
    {
        return [
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'date_of_birth' => '2000-01-01',
            'gender' => $student->gender ?? 'male',
            'enrollment_date' => $student->enrollment_date ?? now()->format('Y-m-d'),
            'status' => $student->status ?? 'active',
        ];
    }
}
