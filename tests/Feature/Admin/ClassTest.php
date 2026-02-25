<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassCourse;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClassTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeClass(array $overrides = []): ClassModel
    {
        return ClassModel::factory()->create($overrides);
    }

    private function validPayload(array $overrides = []): array
    {
        $academicYear = AcademicYear::factory()->create();

        return array_merge([
            'academic_year_id' => $academicYear->id,
            'class_number'     => '42',
            'ngb_number'       => 'NGB-TEST-001',
            'status'           => 'forming',
        ], $overrides);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.classes.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_class_list(): void
    {
        ClassModel::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.classes.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Classes/Index')
                ->has('classes')
                ->has('academicYears')
                ->has('filters')
            );
    }

    public function test_index_filters_by_academic_year(): void
    {
        $academicYear = AcademicYear::factory()->create();
        ClassModel::factory()->create(['academic_year_id' => $academicYear->id]);
        ClassModel::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.classes.index', ['academic_year_id' => $academicYear->id]))
            ->assertInertia(fn (Assert $p) => $p->where('classes.total', 1));
    }

    public function test_index_filters_by_status(): void
    {
        ClassModel::factory()->create(['status' => 'active']);
        ClassModel::factory()->count(2)->create(['status' => 'forming']);

        $this->actingAs($this->admin())
            ->get(route('admin.classes.index', ['status' => 'active']))
            ->assertInertia(fn (Assert $p) => $p->where('classes.total', 1));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form_with_options(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.classes.create'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Classes/Create')
                ->has('academicYears')
            );
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_class_and_redirects(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), $payload);

        $class = ClassModel::first();
        $response->assertRedirect(route('admin.classes.show', $class));
        $this->assertDatabaseHas('classes', ['class_number' => '42']);
    }

    public function test_store_does_not_auto_create_class_courses(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), $this->validPayload());

        $class = ClassModel::first();
        $this->assertNotNull($class);
        $this->assertDatabaseMissing('class_courses', ['class_id' => $class->id]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), [])
            ->assertSessionHasErrors([
                'academic_year_id', 'class_number', 'ngb_number', 'status',
            ]);
    }

    public function test_store_validates_status_enum(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), $this->validPayload(['status' => 'invalid']))
            ->assertSessionHasErrors(['status']);
    }

    public function test_store_validates_ngb_number_unique(): void
    {
        $existing = $this->makeClass();

        $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), $this->validPayload([
                'ngb_number' => $existing->ngb_number,
            ]))
            ->assertSessionHasErrors(['ngb_number']);
    }

    public function test_store_validates_academic_year_exists(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), $this->validPayload([
                'academic_year_id' => 99999,
            ]))
            ->assertSessionHasErrors(['academic_year_id']);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_renders_class_detail(): void
    {
        $class = $this->makeClass();

        $this->actingAs($this->admin())
            ->get(route('admin.classes.show', $class))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Classes/Show')
                ->has('class')
            );
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form(): void
    {
        $class = $this->makeClass();

        $this->actingAs($this->admin())
            ->get(route('admin.classes.edit', $class))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Classes/Edit')
                ->has('class')
                ->has('academicYears')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_class(): void
    {
        $class = $this->makeClass();

        $response = $this->actingAs($this->admin())
            ->patch(route('admin.classes.update', $class), [
                'academic_year_id' => $class->academic_year_id,
                'class_number'     => '99',
                'ngb_number'       => $class->ngb_number,
                'status'           => 'active',
            ]);

        $response->assertRedirect(route('admin.classes.show', $class));
        $this->assertDatabaseHas('classes', ['id' => $class->id, 'class_number' => '99', 'status' => 'active']);
    }

    public function test_update_validates_required_fields(): void
    {
        $class = $this->makeClass();

        $this->actingAs($this->admin())
            ->patch(route('admin.classes.update', $class), [])
            ->assertSessionHasErrors(['academic_year_id', 'class_number', 'ngb_number', 'status']);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_empty_class(): void
    {
        $class = $this->makeClass();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.classes.destroy', $class));

        $response->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }

    public function test_destroy_blocked_when_class_courses_have_enrollments(): void
    {
        $class   = $this->makeClass();
        $cc      = ClassCourse::factory()->create(['class_id' => $class->id]);
        $student = Student::factory()->create();
        Enrollment::factory()->create([
            'class_course_id' => $cc->id,
            'student_id'      => $student->id,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.classes.destroy', $class))
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('classes', ['id' => $class->id]);
    }
}
