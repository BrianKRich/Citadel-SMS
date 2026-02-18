<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Term;
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
        $term         = Term::factory()->for($academicYear)->create();
        $course       = Course::factory()->create();
        $employee     = Employee::factory()->create();

        return array_merge([
            'course_id'        => $course->id,
            'employee_id'      => $employee->id,
            'academic_year_id' => $academicYear->id,
            'term_id'          => $term->id,
            'section_name'     => 'A',
            'max_students'     => 30,
            'status'           => 'open',
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
                ->has('terms')
                ->has('courses')
                ->has('employees')
                ->has('filters')
            );
    }

    public function test_index_filters_by_term(): void
    {
        $academicYear = AcademicYear::factory()->create();
        $term         = Term::factory()->for($academicYear)->create();
        ClassModel::factory()->create(['term_id' => $term->id, 'academic_year_id' => $academicYear->id]);
        ClassModel::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.classes.index', ['term_id' => $term->id]))
            ->assertInertia(fn (Assert $p) => $p->where('classes.total', 1));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form_with_options(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.classes.create'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Classes/Create')
                ->has('courses')
                ->has('employees')
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
        $this->assertDatabaseHas('classes', ['section_name' => 'A']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), [])
            ->assertSessionHasErrors([
                'course_id', 'employee_id', 'academic_year_id',
                'term_id', 'section_name', 'max_students', 'status',
            ]);
    }

    public function test_store_validates_status_enum(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), $this->validPayload(['status' => 'invalid']))
            ->assertSessionHasErrors(['status']);
    }

    public function test_store_detects_schedule_conflict(): void
    {
        $academicYear = AcademicYear::factory()->create();
        $term         = Term::factory()->for($academicYear)->create();
        $course       = Course::factory()->create();
        $employee     = Employee::factory()->create();

        // Existing class on Monday 08:00–09:00
        ClassModel::factory()->create([
            'employee_id'      => $employee->id,
            'term_id'          => $term->id,
            'academic_year_id' => $academicYear->id,
            'schedule'         => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00']],
        ]);

        // New class overlaps Monday 08:30–09:30
        $this->actingAs($this->admin())
            ->post(route('admin.classes.store'), [
                'course_id'        => $course->id,
                'employee_id'      => $employee->id,
                'academic_year_id' => $academicYear->id,
                'term_id'          => $term->id,
                'section_name'     => 'B',
                'max_students'     => 30,
                'status'           => 'open',
                'schedule'         => [['day' => 'Monday', 'start_time' => '08:30', 'end_time' => '09:30']],
            ])
            ->assertSessionHasErrors(['schedule']);
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
                ->has('courses')
                ->has('employees')
                ->has('academicYears')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_class(): void
    {
        $class = $this->makeClass();

        $response = $this->actingAs($this->admin())
            ->patch(route('admin.classes.update', $class), [
                'course_id'        => $class->course_id,
                'employee_id'      => $class->employee_id,
                'academic_year_id' => $class->academic_year_id,
                'term_id'          => $class->term_id,
                'section_name'     => 'Updated',
                'max_students'     => 25,
                'status'           => 'open',
            ]);

        $response->assertRedirect(route('admin.classes.show', $class));
        $this->assertDatabaseHas('classes', ['id' => $class->id, 'section_name' => 'Updated']);
    }

    public function test_update_rejects_reducing_capacity_below_enrollment(): void
    {
        $class    = $this->makeClass(['max_students' => 5]);
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();
        Enrollment::factory()->create(['class_id' => $class->id, 'student_id' => $student1->id, 'status' => 'enrolled']);
        Enrollment::factory()->create(['class_id' => $class->id, 'student_id' => $student2->id, 'status' => 'enrolled']);

        $this->actingAs($this->admin())
            ->patch(route('admin.classes.update', $class), [
                'course_id'        => $class->course_id,
                'employee_id'      => $class->employee_id,
                'academic_year_id' => $class->academic_year_id,
                'term_id'          => $class->term_id,
                'section_name'     => $class->section_name,
                'max_students'     => 1,  // below enrolled count of 2
                'status'           => 'open',
            ])
            ->assertSessionHasErrors(['max_students']);
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

    public function test_destroy_blocked_when_enrollments_exist(): void
    {
        $class   = $this->makeClass();
        $student = Student::factory()->create();
        Enrollment::factory()->create(['class_id' => $class->id, 'student_id' => $student->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.classes.destroy', $class))
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('classes', ['id' => $class->id]);
    }
}
