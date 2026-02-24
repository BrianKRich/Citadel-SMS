<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\CohortCourse;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * Create a CohortCourse by using an auto-created cohort from ClassModel::boot().
     * Using CohortCourse::factory() directly would fail because CohortFactory tries
     * to insert a duplicate cohort (alpha/bravo) for the same class_id.
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

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.enrollment.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_enrollment_list(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.enrollment.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Enrollment/Index')
                ->has('enrollments')
                ->has('students')
                ->has('classes')
                ->has('filters')
            );
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_enrollment_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.enrollment.create'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Enrollment/Create')
                ->has('students')
                ->has('cohortCourses')
                ->has('classes')
            );
    }

    // ── Enroll ────────────────────────────────────────────────────────────────

    public function test_enroll_creates_enrollment_and_redirects(): void
    {
        $student = Student::factory()->create();
        $cc      = $this->makeCohortCourse(['status' => 'open', 'max_students' => 30]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.enrollment.enroll'), [
                'student_id'       => $student->id,
                'cohort_course_id' => $cc->id,
                'enrollment_date'  => '2024-08-15',
            ]);

        $response->assertRedirect(route('admin.enrollment.index'));
        $this->assertDatabaseHas('enrollments', [
            'student_id'       => $student->id,
            'cohort_course_id' => $cc->id,
            'status'           => 'enrolled',
        ]);
    }

    public function test_enroll_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.enrollment.enroll'), [])
            ->assertSessionHasErrors(['student_id', 'cohort_course_id']);
    }

    public function test_enroll_rejects_duplicate_enrollment(): void
    {
        $student = Student::factory()->create();
        $cc      = $this->makeCohortCourse(['status' => 'open', 'max_students' => 30]);
        Enrollment::factory()->create([
            'student_id'       => $student->id,
            'cohort_course_id' => $cc->id,
            'status'           => 'enrolled',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.enrollment.enroll'), [
                'student_id'       => $student->id,
                'cohort_course_id' => $cc->id,
            ])
            ->assertSessionHasErrors(['error']);
    }

    public function test_enroll_rejects_full_cohort_course(): void
    {
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();
        $cc       = $this->makeCohortCourse(['status' => 'open', 'max_students' => 1]);
        Enrollment::factory()->create([
            'student_id'       => $student1->id,
            'cohort_course_id' => $cc->id,
            'status'           => 'enrolled',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.enrollment.enroll'), [
                'student_id'       => $student2->id,
                'cohort_course_id' => $cc->id,
            ])
            ->assertSessionHasErrors(['error']);
    }

    public function test_enroll_rejects_non_open_cohort_course(): void
    {
        $student = Student::factory()->create();
        $cc      = $this->makeCohortCourse(['status' => 'closed', 'max_students' => 30]);

        $this->actingAs($this->admin())
            ->post(route('admin.enrollment.enroll'), [
                'student_id'       => $student->id,
                'cohort_course_id' => $cc->id,
            ])
            ->assertSessionHasErrors(['error']);
    }

    // ── Drop ──────────────────────────────────────────────────────────────────

    public function test_drop_updates_enrollment_status_to_dropped(): void
    {
        $cc         = $this->makeCohortCourse();
        $enrollment = Enrollment::factory()->create([
            'cohort_course_id' => $cc->id,
            'status'           => 'enrolled',
        ]);

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.enrollment.drop', $enrollment));

        $response->assertRedirect(route('admin.enrollment.index'));
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'status' => 'dropped']);
    }

    // ── Student Schedule ──────────────────────────────────────────────────────

    public function test_student_schedule_renders(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.enrollment.student-schedule', $student))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Enrollment/StudentSchedule')
                ->has('student')
                ->has('enrollments')
            );
    }
}
