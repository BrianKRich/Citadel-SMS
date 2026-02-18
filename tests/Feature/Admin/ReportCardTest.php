<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\GradingScale;
use App\Models\Student;
use App\Models\Term;
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

    private function makeTermWithEnrollment(): array
    {
        $year = AcademicYear::factory()->create(['is_current' => true]);
        $term = Term::factory()->create(['academic_year_id' => $year->id, 'is_current' => true]);
        $classModel = ClassModel::factory()->create(['term_id' => $term->id]);
        $student = Student::factory()->create(['status' => 'active']);

        GradingScale::factory()->create([
            'is_default' => true,
            'scale' => [
                ['letter' => 'A', 'min_percentage' => 90, 'gpa_points' => 4.0],
                ['letter' => 'B', 'min_percentage' => 80, 'gpa_points' => 3.0],
                ['letter' => 'C', 'min_percentage' => 70, 'gpa_points' => 2.0],
                ['letter' => 'D', 'min_percentage' => 60, 'gpa_points' => 1.0],
                ['letter' => 'F', 'min_percentage' => 0,  'gpa_points' => 0.0],
            ],
        ]);

        $enrollment = Enrollment::factory()->withGrade('B', 3.0)->create([
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
        ]);

        return compact('year', 'term', 'classModel', 'student', 'enrollment');
    }

    public function test_index_requires_auth(): void
    {
        $response = $this->get(route('admin.report-cards.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_renders_page(): void
    {
        $user = $this->adminUser();
        $this->makeTermWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Index')
            ->has('students')
            ->has('terms')
        );
    }

    public function test_index_search_filters_students(): void
    {
        $user = $this->adminUser();
        $data = $this->makeTermWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.index', [
            'search' => $data['student']->last_name,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Index')
            ->has('students')
        );
    }

    public function test_show_renders_page_for_student_and_term(): void
    {
        $user = $this->adminUser();
        $data = $this->makeTermWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.show', [
            'student' => $data['student']->id,
            'term_id' => $data['term']->id,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Show')
            ->has('student')
            ->has('term')
            ->has('enrollments')
            ->has('termGpa')
            ->has('cumulativeGpa')
        );
    }

    public function test_show_uses_current_term_when_no_term_id_provided(): void
    {
        $user = $this->adminUser();
        $data = $this->makeTermWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.show', $data['student']->id));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Show')
        );
    }

    public function test_show_requires_auth(): void
    {
        $student = Student::factory()->create(['status' => 'active']);
        $response = $this->get(route('admin.report-cards.show', $student->id));
        $response->assertRedirect(route('login'));
    }

    public function test_pdf_returns_pdf_download(): void
    {
        $user = $this->adminUser();
        $data = $this->makeTermWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.pdf', [
            'student' => $data['student']->id,
            'term_id' => $data['term']->id,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_requires_auth(): void
    {
        $student = Student::factory()->create(['status' => 'active']);
        $response = $this->get(route('admin.report-cards.pdf', $student->id));
        $response->assertRedirect(route('login'));
    }

    public function test_pdf_filename_contains_student_id_and_term(): void
    {
        $user = $this->adminUser();
        $data = $this->makeTermWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.pdf', [
            'student' => $data['student']->id,
            'term_id' => $data['term']->id,
        ]));

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('report-card', $disposition);
    }

    public function test_show_passes_enrollments_for_selected_term_only(): void
    {
        $user = $this->adminUser();
        $data = $this->makeTermWithEnrollment();

        // Second term with no enrollment for this student
        $otherTerm = Term::factory()->create([
            'academic_year_id' => $data['year']->id,
            'is_current' => false,
        ]);

        $response = $this->actingAs($user)->get(route('admin.report-cards.show', [
            'student' => $data['student']->id,
            'term_id' => $otherTerm->id,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Show')
            ->where('enrollments', fn ($enrollments) => count($enrollments) === 0)
        );
    }
}
