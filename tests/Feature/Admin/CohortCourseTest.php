<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\CohortCourse;
use App\Models\Course;
use App\Models\EducationalInstitution;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CohortCourseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * Create a CohortCourse using an auto-created cohort from ClassModel::boot()
     * to avoid unique constraint violations on (class_id, name).
     */
    private function makeCohortCourse(array $overrides = []): CohortCourse
    {
        $class  = ClassModel::factory()->create();
        $cohort = $class->cohorts()->first();

        return CohortCourse::factory()->create(array_merge(
            ['cohort_id' => $cohort->id],
            $overrides
        ));
    }

    private function validStaffPayload(array $overrides = []): array
    {
        $class    = ClassModel::factory()->create();
        $cohort   = $class->cohorts()->first();
        $course   = Course::factory()->create();
        $employee = Employee::factory()->create();

        return array_merge([
            'cohort_id'       => $cohort->id,
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
        $this->get(route('admin.cohort-courses.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_cohort_course_list(): void
    {
        $this->makeCohortCourse();
        $this->makeCohortCourse();

        $this->actingAs($this->admin())
            ->get(route('admin.cohort-courses.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/CohortCourses/Index')
                ->has('cohortCourses')
                ->has('filters')
            );
    }

    public function test_index_paginates_results(): void
    {
        // Create 15 cohort courses each with their own class+cohorts
        for ($i = 0; $i < 15; $i++) {
            $this->makeCohortCourse();
        }

        $this->actingAs($this->admin())
            ->get(route('admin.cohort-courses.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->where('cohortCourses.per_page', 10)
                ->where('cohortCourses.total', 15)
            );
    }

    public function test_index_filters_by_status(): void
    {
        $this->makeCohortCourse(['status' => 'open']);
        $this->makeCohortCourse(['status' => 'closed']);

        $this->actingAs($this->admin())
            ->get(route('admin.cohort-courses.index', ['status' => 'open']))
            ->assertInertia(fn (Assert $p) => $p
                ->where('cohortCourses.total', 1)
            );
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form_with_options(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.cohort-courses.create'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/CohortCourses/Create')
                ->has('classes')
                ->has('courses')
                ->has('employees')
                ->has('institutions')
            );
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_cohort_course_with_staff_instructor(): void
    {
        $payload = $this->validStaffPayload();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.cohort-courses.store'), $payload);

        $cc = CohortCourse::latest()->first();
        $response->assertRedirect(route('admin.cohort-courses.show', $cc));
        $this->assertDatabaseHas('cohort_courses', [
            'cohort_id'       => $payload['cohort_id'],
            'course_id'       => $payload['course_id'],
            'instructor_type' => 'staff',
            'employee_id'     => $payload['employee_id'],
        ]);
    }

    public function test_store_creates_cohort_course_with_institution_instructor(): void
    {
        $class       = ClassModel::factory()->create();
        $cohort      = $class->cohorts()->first();
        $course      = Course::factory()->create();
        $institution = EducationalInstitution::factory()->create(['type' => 'technical_college']);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.cohort-courses.store'), [
                'cohort_id'       => $cohort->id,
                'course_id'       => $course->id,
                'instructor_type' => 'technical_college',
                'employee_id'     => null,
                'institution_id'  => $institution->id,
                'max_students'    => 25,
                'status'          => 'open',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cohort_courses', [
            'instructor_type' => 'technical_college',
            'institution_id'  => $institution->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.cohort-courses.store'), [])
            ->assertSessionHasErrors([
                'cohort_id', 'course_id', 'instructor_type', 'max_students', 'status',
            ]);
    }

    public function test_store_validates_instructor_type_enum(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.cohort-courses.store'), $this->validStaffPayload([
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
            ->post(route('admin.cohort-courses.store'), $payload)
            ->assertStatus(422);
    }

    public function test_store_validates_technical_college_requires_institution_id(): void
    {
        $class  = ClassModel::factory()->create();
        $cohort = $class->cohorts()->first();
        $course = Course::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.cohort-courses.store'), [
                'cohort_id'       => $cohort->id,
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
        $cohort = $class->cohorts()->first();
        $course = Course::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.cohort-courses.store'), [
                'cohort_id'       => $cohort->id,
                'course_id'       => $course->id,
                'instructor_type' => 'university',
                'employee_id'     => null,
                'institution_id'  => null,
                'max_students'    => 30,
                'status'          => 'open',
            ])
            ->assertStatus(422);
    }

    public function test_store_validates_cohort_exists(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.cohort-courses.store'), $this->validStaffPayload([
                'cohort_id' => 99999,
            ]))
            ->assertSessionHasErrors(['cohort_id']);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_renders_cohort_course_detail(): void
    {
        $cc = $this->makeCohortCourse();

        $this->actingAs($this->admin())
            ->get(route('admin.cohort-courses.show', $cc))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/CohortCourses/Show')
                ->has('cohortCourse')
            );
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form(): void
    {
        $cc = $this->makeCohortCourse();

        $this->actingAs($this->admin())
            ->get(route('admin.cohort-courses.edit', $cc))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/CohortCourses/Edit')
                ->has('cohortCourse')
                ->has('classes')
                ->has('courses')
                ->has('employees')
                ->has('institutions')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_cohort_course(): void
    {
        $cc       = $this->makeCohortCourse(['status' => 'open', 'max_students' => 20]);
        $course   = Course::factory()->create();
        $cohort   = $cc->cohort;
        $employee = Employee::factory()->create();

        $response = $this->actingAs($this->admin())
            ->put(route('admin.cohort-courses.update', $cc), [
                'cohort_id'       => $cohort->id,
                'course_id'       => $course->id,
                'instructor_type' => 'staff',
                'employee_id'     => $employee->id,
                'institution_id'  => null,
                'max_students'    => 35,
                'status'          => 'closed',
            ]);

        $response->assertRedirect(route('admin.cohort-courses.show', $cc));
        $this->assertDatabaseHas('cohort_courses', [
            'id'           => $cc->id,
            'max_students' => 35,
            'status'       => 'closed',
        ]);
    }

    public function test_update_validates_required_fields(): void
    {
        $cc = $this->makeCohortCourse();

        $this->actingAs($this->admin())
            ->put(route('admin.cohort-courses.update', $cc), [])
            ->assertSessionHasErrors([
                'cohort_id', 'course_id', 'instructor_type', 'max_students', 'status',
            ]);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_empty_cohort_course(): void
    {
        $cc = $this->makeCohortCourse();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.cohort-courses.destroy', $cc));

        $response->assertRedirect(route('admin.cohort-courses.index'));
        $this->assertDatabaseMissing('cohort_courses', ['id' => $cc->id]);
    }

    public function test_destroy_blocked_when_enrollments_exist(): void
    {
        $cc      = $this->makeCohortCourse();
        $student = Student::factory()->create();
        Enrollment::factory()->create([
            'cohort_course_id' => $cc->id,
            'student_id'       => $student->id,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.cohort-courses.destroy', $cc))
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('cohort_courses', ['id' => $cc->id]);
    }
}
