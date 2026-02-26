<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\ClassCourse;
use App\Models\Enrollment;
use App\Models\GradingScale;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TranscriptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('feature_transcripts_enabled', '1', 'boolean');
    }

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeStudentWithHistory(): Student
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

        $class       = ClassModel::factory()->create(['start_date' => '2025-08-01', 'end_date' => '2025-12-15']);
        $classCourse = ClassCourse::factory()->create(['class_id' => $class->id]);
        $student     = Student::factory()->create(['status' => 'active']);

        Enrollment::factory()->withGrade('A', 4.0)->create([
            'student_id'      => $student->id,
            'class_course_id' => $classCourse->id,
        ]);

        return $student;
    }

    public function test_index_requires_auth(): void
    {
        $response = $this->get(route('admin.transcripts.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_renders_page(): void
    {
        $user = $this->adminUser();
        $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Transcripts/Index')
            ->has('students')
        );
    }

    public function test_index_search_filters_students(): void
    {
        $user    = $this->adminUser();
        $student = $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.index', [
            'search' => $student->last_name,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Transcripts/Index')
            ->has('students')
        );
    }

    public function test_show_renders_page(): void
    {
        $user    = $this->adminUser();
        $student = $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.show', $student->id));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Transcripts/Show')
            ->has('student')
            ->has('classGroups')
            ->has('totalCredits')
            ->has('cumulativeGpa')
            ->where('official', false)
        );
    }

    public function test_show_marks_official_when_flag_set(): void
    {
        $user    = $this->adminUser();
        $student = $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.show', [
            'student'  => $student->id,
            'official' => '1',
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Transcripts/Show')
            ->where('official', true)
        );
    }

    public function test_show_requires_auth(): void
    {
        $student  = Student::factory()->create(['status' => 'active']);
        $response = $this->get(route('admin.transcripts.show', $student->id));
        $response->assertRedirect(route('login'));
    }

    public function test_show_groups_enrollments_by_class(): void
    {
        $user    = $this->adminUser();
        $student = $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.show', $student->id));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Transcripts/Show')
            ->where('classGroups', fn ($groups) => count($groups) === 1)
        );
    }

    public function test_pdf_returns_unofficial_pdf(): void
    {
        $user    = $this->adminUser();
        $student = $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.pdf', $student->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_returns_official_pdf(): void
    {
        $user    = $this->adminUser();
        $student = $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.pdf', [
            'student'  => $student->id,
            'official' => '1',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_requires_auth(): void
    {
        $student  = Student::factory()->create(['status' => 'active']);
        $response = $this->get(route('admin.transcripts.pdf', $student->id));
        $response->assertRedirect(route('login'));
    }

    public function test_unofficial_pdf_filename_contains_unofficial(): void
    {
        $user    = $this->adminUser();
        $student = $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.pdf', $student->id));

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('unofficial', $disposition);
    }

    public function test_official_pdf_filename_contains_official(): void
    {
        $user    = $this->adminUser();
        $student = $this->makeStudentWithHistory();

        $response = $this->actingAs($user)->get(route('admin.transcripts.pdf', [
            'student'  => $student->id,
            'official' => '1',
        ]));

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('official', $disposition);
    }
}
