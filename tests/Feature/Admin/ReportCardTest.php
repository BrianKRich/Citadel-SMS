<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\CohortCourse;
use App\Models\Enrollment;
use App\Models\GradingScale;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeCohortWithEnrollment(): array
    {
        GradingScale::factory()->create([
            'is_default' => true,
            'scale'      => [
                ['letter' => 'A', 'min_percentage' => 90, 'gpa_points' => 4.0],
                ['letter' => 'B', 'min_percentage' => 80, 'gpa_points' => 3.0],
                ['letter' => 'C', 'min_percentage' => 70, 'gpa_points' => 2.0],
                ['letter' => 'D', 'min_percentage' => 60, 'gpa_points' => 1.0],
                ['letter' => 'F', 'min_percentage' => 0,  'gpa_points' => 0.0],
            ],
        ]);

        $class  = ClassModel::factory()->create();
        // Use the auto-created alpha cohort from boot()
        $cohort = $class->cohorts()->where('name', 'alpha')->first();
        $cohort->update(['start_date' => '2025-01-01', 'end_date' => '2025-06-30']);

        $cohortCourse = CohortCourse::factory()->create(['cohort_id' => $cohort->id]);
        $student      = Student::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->withGrade('B', 3.0)->create([
            'student_id'       => $student->id,
            'cohort_course_id' => $cohortCourse->id,
        ]);

        return compact('class', 'cohort', 'cohortCourse', 'student', 'enrollment');
    }

    public function test_index_requires_auth(): void
    {
        $response = $this->get(route('admin.report-cards.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_renders_page(): void
    {
        $user = $this->adminUser();
        $this->makeCohortWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Index')
            ->has('students')
            ->has('cohorts')
        );
    }

    public function test_index_search_filters_students(): void
    {
        $user = $this->adminUser();
        $data = $this->makeCohortWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.index', [
            'search' => $data['student']->last_name,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Index')
            ->has('students')
        );
    }

    public function test_show_renders_page_for_student_and_cohort(): void
    {
        $user = $this->adminUser();
        $data = $this->makeCohortWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.show', [
            'student'   => $data['student']->id,
            'cohort_id' => $data['cohort']->id,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Show')
            ->has('student')
            ->has('cohort')
            ->has('enrollments')
            ->has('cohortGpa')
            ->has('cumulativeGpa')
        );
    }

    public function test_show_uses_first_cohort_when_no_cohort_id_provided(): void
    {
        $user = $this->adminUser();
        $data = $this->makeCohortWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.show', $data['student']->id));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Show')
        );
    }

    public function test_show_requires_auth(): void
    {
        $student  = Student::factory()->create(['status' => 'active']);
        $response = $this->get(route('admin.report-cards.show', $student->id));
        $response->assertRedirect(route('login'));
    }

    public function test_pdf_returns_pdf_download(): void
    {
        $user = $this->adminUser();
        $data = $this->makeCohortWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.pdf', [
            'student'   => $data['student']->id,
            'cohort_id' => $data['cohort']->id,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_requires_auth(): void
    {
        $student  = Student::factory()->create(['status' => 'active']);
        $response = $this->get(route('admin.report-cards.pdf', $student->id));
        $response->assertRedirect(route('login'));
    }

    public function test_pdf_filename_contains_report_card(): void
    {
        $user = $this->adminUser();
        $data = $this->makeCohortWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.pdf', [
            'student'   => $data['student']->id,
            'cohort_id' => $data['cohort']->id,
        ]));

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('report-card', $disposition);
    }

    public function test_show_passes_enrollments_for_selected_cohort_only(): void
    {
        $user = $this->adminUser();
        $data = $this->makeCohortWithEnrollment();

        // Use the bravo cohort (auto-created by boot), which has no enrollment for this student
        $otherCohort = $data['class']->cohorts()->where('name', 'bravo')->first();
        $otherCohort->update(['start_date' => '2025-07-01', 'end_date' => '2025-12-31']);

        $response = $this->actingAs($user)->get(route('admin.report-cards.show', [
            'student'   => $data['student']->id,
            'cohort_id' => $otherCohort->id,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Show')
            ->where('enrollments', fn ($enrollments) => count($enrollments) === 0)
        );
    }
}
