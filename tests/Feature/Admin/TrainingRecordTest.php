<?php

namespace Tests\Feature\Admin;

use App\Models\Document;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\TrainingCourse;
use App\Models\TrainingRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrainingRecordTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function enableTraining(): void
    {
        Setting::set('feature_staff_training_enabled', '1', 'boolean');
    }

    private function enableDocuments(): void
    {
        Setting::set('feature_documents_enabled', '1', 'boolean');
    }

    // -----------------------------------------------------------------------
    // 1. All routes blocked when feature disabled
    // -----------------------------------------------------------------------

    public function test_all_routes_blocked_when_feature_disabled(): void
    {
        $admin  = $this->adminUser();
        $record = TrainingRecord::factory()->create();

        $this->actingAs($admin)->get(route('admin.training-records.index'))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.training-records.create'))->assertStatus(403);
        $this->actingAs($admin)->post(route('admin.training-records.store'))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.training-records.show', $record))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.training-records.edit', $record))->assertStatus(403);
        $this->actingAs($admin)->put(route('admin.training-records.update', $record))->assertStatus(403);
        $this->actingAs($admin)->delete(route('admin.training-records.destroy', $record))->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // 2. Index renders
    // -----------------------------------------------------------------------

    public function test_index_renders(): void
    {
        $this->enableTraining();
        $admin = $this->adminUser();
        TrainingRecord::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.training-records.index'))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Records/Index')
                     ->has('records.data', 3)
                     ->has('courses')
                     ->has('employees')
            );
    }

    public function test_index_filters_by_employee(): void
    {
        $this->enableTraining();
        $admin    = $this->adminUser();
        $employee = Employee::factory()->create();

        TrainingRecord::factory()->count(2)->create(['employee_id' => $employee->id]);
        TrainingRecord::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.training-records.index', ['employee_id' => $employee->id]))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Records/Index')
                     ->has('records.data', 2)
            );
    }

    public function test_index_filters_by_course(): void
    {
        $this->enableTraining();
        $admin  = $this->adminUser();
        $course = TrainingCourse::factory()->create();

        TrainingRecord::factory()->count(2)->create(['training_course_id' => $course->id]);
        TrainingRecord::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.training-records.index', ['training_course_id' => $course->id]))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Records/Index')
                     ->has('records.data', 2)
            );
    }

    // -----------------------------------------------------------------------
    // 3. Create/Store
    // -----------------------------------------------------------------------

    public function test_create_renders(): void
    {
        $this->enableTraining();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.training-records.create'))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Records/Create')
                     ->has('courses')
                     ->has('employees')
                     ->where('preselectedEmployee', [])
            );
    }

    public function test_can_create_single_record(): void
    {
        $this->enableTraining();
        $admin    = $this->adminUser();
        $employee = Employee::factory()->create();
        $course   = TrainingCourse::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.training-records.store'), [
                'employee_ids'       => [$employee->id],
                'training_course_id' => $course->id,
                'date_completed'     => '2026-01-15',
                'trainer_name'       => 'Jane Smith',
                'notes'              => 'Completed all modules',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('training_records', [
            'employee_id'        => $employee->id,
            'training_course_id' => $course->id,
            'trainer_name'       => 'Jane Smith',
        ]);
    }

    public function test_can_create_batch_records(): void
    {
        $this->enableTraining();
        $admin     = $this->adminUser();
        $employees = Employee::factory()->count(4)->create();
        $course    = TrainingCourse::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.training-records.store'), [
                'employee_ids'       => $employees->pluck('id')->toArray(),
                'training_course_id' => $course->id,
                'date_completed'     => '2026-01-20',
                'trainer_name'       => 'Bob Trainer',
            ])
            ->assertRedirect(route('admin.training-records.index'))
            ->assertSessionHas('success', '4 training completions logged successfully.');

        $this->assertDatabaseCount('training_records', 4);
        foreach ($employees as $emp) {
            $this->assertDatabaseHas('training_records', [
                'employee_id'        => $emp->id,
                'training_course_id' => $course->id,
            ]);
        }
    }

    public function test_store_requires_required_fields(): void
    {
        $this->enableTraining();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.training-records.store'), [])
            ->assertSessionHasErrors(['employee_ids', 'training_course_id', 'date_completed', 'trainer_name']);
    }

    // -----------------------------------------------------------------------
    // 4. Show
    // -----------------------------------------------------------------------

    public function test_show_renders(): void
    {
        $this->enableTraining();
        $admin  = $this->adminUser();
        $record = TrainingRecord::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.training-records.show', $record))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Records/Show')
                     ->has('record')
                     ->where('documentsEnabled', false)
                     ->has('documents', 0)
            );
    }

    public function test_show_includes_documents_when_enabled(): void
    {
        $this->enableTraining();
        $this->enableDocuments();
        Storage::fake('local');
        $admin  = $this->adminUser();
        $record = TrainingRecord::factory()->create();

        // Upload 2 docs for this record
        Document::factory()->count(2)->create([
            'entity_type' => 'TrainingRecord',
            'entity_id'   => $record->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.training-records.show', $record))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Records/Show')
                     ->where('documentsEnabled', true)
                     ->has('documents', 2)
            );
    }

    // -----------------------------------------------------------------------
    // 5. Edit/Update
    // -----------------------------------------------------------------------

    public function test_edit_renders(): void
    {
        $this->enableTraining();
        $admin  = $this->adminUser();
        $record = TrainingRecord::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.training-records.edit', $record))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Records/Edit')
                     ->has('record')
            );
    }

    public function test_can_update_record(): void
    {
        $this->enableTraining();
        $admin    = $this->adminUser();
        $record   = TrainingRecord::factory()->create();
        $employee = Employee::factory()->create();
        $course   = TrainingCourse::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.training-records.update', $record), [
                'employee_id'        => $employee->id,
                'training_course_id' => $course->id,
                'date_completed'     => '2026-02-01',
                'trainer_name'       => 'Bob Jones',
                'notes'              => 'Updated notes',
            ])
            ->assertRedirect(route('admin.training-records.show', $record));

        $this->assertDatabaseHas('training_records', [
            'id'          => $record->id,
            'trainer_name' => 'Bob Jones',
        ]);
    }

    // -----------------------------------------------------------------------
    // 6. Destroy
    // -----------------------------------------------------------------------

    public function test_can_delete_record(): void
    {
        $this->enableTraining();
        $admin  = $this->adminUser();
        $record = TrainingRecord::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.training-records.destroy', $record))
            ->assertRedirect(route('admin.training-records.index'));

        $this->assertDatabaseMissing('training_records', ['id' => $record->id]);
    }

    // -----------------------------------------------------------------------
    // 7. Document upload for TrainingRecord
    // -----------------------------------------------------------------------

    public function test_can_upload_document_for_training_record(): void
    {
        $this->enableTraining();
        $this->enableDocuments();
        Storage::fake('local');
        $admin  = $this->adminUser();
        $record = TrainingRecord::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'entity_type' => 'TrainingRecord',
                'entity_id'   => $record->id,
                'file'        => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
                'category'    => 'Certificate',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'entity_type' => 'TrainingRecord',
            'entity_id'   => $record->id,
            'category'    => 'Certificate',
        ]);
    }

    public function test_document_upload_rejects_nonexistent_training_record(): void
    {
        $this->enableTraining();
        $this->enableDocuments();
        Storage::fake('local');
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'entity_type' => 'TrainingRecord',
                'entity_id'   => 9999,
                'file'        => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // 8. Employee show card
    // -----------------------------------------------------------------------

    public function test_employee_show_includes_training_when_enabled(): void
    {
        $this->enableTraining();
        $admin    = $this->adminUser();
        $employee = Employee::factory()->create();

        TrainingRecord::factory()->count(3)->create(['employee_id' => $employee->id]);

        $this->actingAs($admin)
            ->get(route('admin.employees.show', $employee))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Employees/Show')
                     ->where('trainingEnabled', true)
                     ->has('trainingRecords', 3)
            );
    }

    public function test_employee_show_training_empty_when_disabled(): void
    {
        $admin    = $this->adminUser();
        $employee = Employee::factory()->create();

        TrainingRecord::factory()->count(2)->create(['employee_id' => $employee->id]);

        $this->actingAs($admin)
            ->get(route('admin.employees.show', $employee))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Employees/Show')
                     ->where('trainingEnabled', false)
                     ->has('trainingRecords', 0)
            );
    }

    // -----------------------------------------------------------------------
    // 9. Non-admin blocked
    // -----------------------------------------------------------------------

    public function test_non_admin_blocked(): void
    {
        $this->enableTraining();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.training-records.index'))
            ->assertStatus(403);
    }
}
