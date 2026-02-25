<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\ClassCourse;
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

    private function makeClassWithEnrollment(): array
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

        $class       = ClassModel::factory()->create(['start_date' => '2025-01-01', 'end_date' => '2025-06-30']);
        $classCourse = ClassCourse::factory()->create(['class_id' => $class->id]);
        $student     = Student::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->withGrade('B', 3.0)->create([
            'student_id'      => $student->id,
            'class_course_id' => $classCourse->id,
        ]);

        return compact('class', 'classCourse', 'student', 'enrollment');
    }

    public function test_index_requires_auth(): void
    {
        $response = $this->get(route('admin.report-cards.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_renders_page(): void
    {
        $user = $this->adminUser();
        $this->makeClassWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Index')
            ->has('students')
            ->has('classes')
        );
    }

    public function test_index_search_filters_students(): void
    {
        $user = $this->adminUser();
        $data = $this->makeClassWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.index', [
            'search' => $data['student']->last_name,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Index')
            ->has('students')
        );
    }

    public function test_show_renders_page_for_student_and_class(): void
    {
        $user = $this->adminUser();
        $data = $this->makeClassWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.show', [
            'student'  => $data['student']->id,
            'class_id' => $data['class']->id,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Show')
            ->has('student')
            ->has('currentClass')
            ->has('enrollments')
            ->has('classGpa')
            ->has('cumulativeGpa')
        );
    }

    public function test_show_uses_first_class_when_no_class_id_provided(): void
    {
        $user = $this->adminUser();
        $data = $this->makeClassWithEnrollment();

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
        $data = $this->makeClassWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.pdf', [
            'student'  => $data['student']->id,
            'class_id' => $data['class']->id,
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
        $data = $this->makeClassWithEnrollment();

        $response = $this->actingAs($user)->get(route('admin.report-cards.pdf', [
            'student'  => $data['student']->id,
            'class_id' => $data['class']->id,
        ]));

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('report-card', $disposition);
    }

    public function test_show_passes_enrollments_for_selected_class_only(): void
    {
        $user = $this->adminUser();
        $data = $this->makeClassWithEnrollment();

        // Use a different class that has no enrollment for this student
        $otherClass = ClassModel::factory()->create(['start_date' => '2025-07-01', 'end_date' => '2025-12-31']);

        $response = $this->actingAs($user)->get(route('admin.report-cards.show', [
            'student'  => $data['student']->id,
            'class_id' => $otherClass->id,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ReportCards/Show')
            ->where('enrollments', fn ($enrollments) => count($enrollments) === 0)
        );
    }
}
