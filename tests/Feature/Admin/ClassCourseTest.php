<?php

namespace Tests\Feature\Admin;

use App\Models\ClassCourse;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\EducationalInstitution;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClassCourseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeClassCourse(array $overrides = []): ClassCourse
    {
        return ClassCourse::factory()->create($overrides);
    }

    private function validStaffPayload(array $overrides = []): array
    {
        $class    = ClassModel::factory()->create();
        $course   = Course::factory()->create();
        $employee = Employee::factory()->create();

        return array_merge([
            'class_id'        => $class->id,
            'course_id'       => $course->id,
            'instructor_type' => 'staff',
            'employee_id'     => $employee->id,
            'institution_id'  => null,
            'room'            => 'Room 101',
            'max_students'    => 30,
            'status'          => 'open',
        ], $overrides);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.class-courses.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_class_course_list(): void
    {
        $this->makeClassCourse();
        $this->makeClassCourse();

        $this->actingAs($this->admin())
            ->get(route('admin.class-courses.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/ClassCourses/Index')
                ->has('classCourses')
                ->has('filters')
            );
    }

    public function test_index_paginates_results(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->makeClassCourse();
        }

        $this->actingAs($this->admin())
            ->get(route('admin.class-courses.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->where('classCourses.per_page', 10)
                ->where('classCourses.total', 15)
            );
    }

    public function test_index_filters_by_status(): void
    {
        $this->makeClassCourse(['status' => 'open']);
        $this->makeClassCourse(['status' => 'closed']);

        $this->actingAs($this->admin())
            ->get(route('admin.class-courses.index', ['status' => 'open']))
            ->assertInertia(fn (Assert $p) => $p
                ->where('classCourses.total', 1)
            );
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form_with_options(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.class-courses.create'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/ClassCourses/Create')
                ->has('classes')
                ->has('courses')
                ->has('employees')
                ->has('institutions')
            );
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_class_course_with_staff_instructor(): void
    {
        $payload = $this->validStaffPayload();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.class-courses.store'), $payload);

        $cc = ClassCourse::latest()->first();
        $response->assertRedirect(route('admin.class-courses.show', $cc));
        $this->assertDatabaseHas('class_courses', [
            'class_id'        => $payload['class_id'],
            'course_id'       => $payload['course_id'],
            'instructor_type' => 'staff',
            'employee_id'     => $payload['employee_id'],
        ]);
    }

    public function test_store_creates_class_course_with_institution_instructor(): void
    {
        $class       = ClassModel::factory()->create();
        $course      = Course::factory()->create();
        $institution = EducationalInstitution::factory()->create(['type' => 'technical_college']);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.class-courses.store'), [
                'class_id'        => $class->id,
                'course_id'       => $course->id,
                'instructor_type' => 'technical_college',
                'employee_id'     => null,
                'institution_id'  => $institution->id,
                'max_students'    => 25,
                'status'          => 'open',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('class_courses', [
            'instructor_type' => 'technical_college',
            'institution_id'  => $institution->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.class-courses.store'), [])
            ->assertSessionHasErrors([
                'class_id', 'course_id', 'instructor_type', 'max_students', 'status',
            ]);
    }

    public function test_store_validates_instructor_type_enum(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.class-courses.store'), $this->validStaffPayload([
                'instructor_type' => 'invalid_type',
            ]))
            ->assertSessionHasErrors(['instructor_type']);
    }

    public function test_store_validates_staff_requires_employee_id(): void
    {
        $payload = $this->validStaffPayload([
            'instructor_type' => 'staff',
            'employee_id'     => null,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.class-courses.store'), $payload)
            ->assertStatus(422);
    }

    public function test_store_validates_technical_college_requires_institution_id(): void
    {
        $class  = ClassModel::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.class-courses.store'), [
                'class_id'        => $class->id,
                'course_id'       => $course->id,
                'instructor_type' => 'technical_college',
                'employee_id'     => null,
                'institution_id'  => null,
                'max_students'    => 30,
                'status'          => 'open',
            ])
            ->assertStatus(422);
    }

    public function test_store_validates_university_requires_institution_id(): void
    {
        $class  = ClassModel::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.class-courses.store'), [
                'class_id'        => $class->id,
                'course_id'       => $course->id,
                'instructor_type' => 'university',
                'employee_id'     => null,
                'institution_id'  => null,
                'max_students'    => 30,
                'status'          => 'open',
            ])
            ->assertStatus(422);
    }

    public function test_store_validates_class_exists(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.class-courses.store'), $this->validStaffPayload([
                'class_id' => 99999,
            ]))
            ->assertSessionHasErrors(['class_id']);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_renders_class_course_detail(): void
    {
        $cc = $this->makeClassCourse();

        $this->actingAs($this->admin())
            ->get(route('admin.class-courses.show', $cc))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/ClassCourses/Show')
                ->has('classCourse')
            );
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form(): void
    {
        $cc = $this->makeClassCourse();

        $this->actingAs($this->admin())
            ->get(route('admin.class-courses.edit', $cc))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/ClassCourses/Edit')
                ->has('classCourse')
                ->has('classes')
                ->has('courses')
                ->has('employees')
                ->has('institutions')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_class_course(): void
    {
        $cc       = $this->makeClassCourse(['status' => 'open', 'max_students' => 20]);
        $course   = Course::factory()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($this->admin())
            ->put(route('admin.class-courses.update', $cc), [
                'class_id'        => $cc->class_id,
                'course_id'       => $course->id,
                'instructor_type' => 'staff',
                'employee_id'     => $employee->id,
                'institution_id'  => null,
                'max_students'    => 35,
                'status'          => 'closed',
            ]);

        $response->assertRedirect(route('admin.class-courses.show', $cc));
        $this->assertDatabaseHas('class_courses', [
            'id'           => $cc->id,
            'max_students' => 35,
            'status'       => 'closed',
        ]);
    }

    public function test_update_validates_required_fields(): void
    {
        $cc = $this->makeClassCourse();

        $this->actingAs($this->admin())
            ->put(route('admin.class-courses.update', $cc), [])
            ->assertSessionHasErrors([
                'class_id', 'course_id', 'instructor_type', 'max_students', 'status',
            ]);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_empty_class_course(): void
    {
        $cc = $this->makeClassCourse();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.class-courses.destroy', $cc));

        $response->assertRedirect(route('admin.class-courses.index'));
        $this->assertDatabaseMissing('class_courses', ['id' => $cc->id]);
    }

    public function test_destroy_blocked_when_enrollments_exist(): void
    {
        $cc      = $this->makeClassCourse();
        $student = Student::factory()->create();
        Enrollment::factory()->create([
            'class_course_id' => $cc->id,
            'student_id'      => $student->id,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.class-courses.destroy', $cc))
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('class_courses', ['id' => $cc->id]);
    }
}
