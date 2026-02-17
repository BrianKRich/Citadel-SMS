<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradingScale;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * Ensure a default grading scale exists so GradeCalculationService
     * can assign letter grades and GPA points after saving grades.
     */
    private function createDefaultScale(): GradingScale
    {
        return GradingScale::factory()->create([
            'name'       => 'Standard Scale',
            'is_default' => true,
            'scale'      => [
                ['letter' => 'A', 'min_percentage' => 90, 'gpa_points' => 4.0],
                ['letter' => 'B', 'min_percentage' => 80, 'gpa_points' => 3.0],
                ['letter' => 'C', 'min_percentage' => 70, 'gpa_points' => 2.0],
                ['letter' => 'D', 'min_percentage' => 60, 'gpa_points' => 1.0],
                ['letter' => 'F', 'min_percentage' => 0,  'gpa_points' => 0.0],
            ],
        ]);
    }

    public function test_class_grades_displays_matrix(): void
    {
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        $category   = AssessmentCategory::factory()->create(['weight' => 1.0]);
        $assessment = Assessment::factory()->create([
            'class_id'               => $classModel->id,
            'assessment_category_id' => $category->id,
            'status'                 => 'published',
        ]);

        Grade::factory()->create([
            'enrollment_id' => $enrollment->id,
            'assessment_id' => $assessment->id,
            'score'         => 90.0,
            'graded_by'     => $user->id,
            'graded_at'     => now(),
        ]);

        $response = $this->actingAs($user)->get(
            route('admin.grades.class', $classModel)
        );

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Grades/ClassGrades')
            ->has('classModel')
        );
    }

    public function test_enter_displays_bulk_form(): void
    {
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $category   = AssessmentCategory::factory()->create();
        $assessment = Assessment::factory()->create([
            'class_id'               => $classModel->id,
            'assessment_category_id' => $category->id,
            'status'                 => 'published',
        ]);

        Enrollment::factory()->create([
            'class_id' => $classModel->id,
            'status'   => 'enrolled',
        ]);

        $response = $this->actingAs($user)->get(
            route('admin.grades.enter', $assessment)
        );

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Grades/Enter')
            ->has('assessment')
            ->has('enrollments')
        );
    }

    public function test_store_saves_grades_and_recalculates(): void
    {
        $this->createDefaultScale();

        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $category   = AssessmentCategory::factory()->create(['weight' => 1.0]);
        $assessment = Assessment::factory()->create([
            'class_id'               => $classModel->id,
            'assessment_category_id' => $category->id,
            'max_score'              => 100,
            'is_extra_credit'        => false,
            'status'                 => 'published',
        ]);

        $student    = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        $response = $this->actingAs($user)->post(route('admin.grades.store'), [
            'grades' => [
                [
                    'enrollment_id' => $enrollment->id,
                    'assessment_id' => $assessment->id,
                    'score'         => 92.0,
                    'is_late'       => false,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('grades', [
            'enrollment_id' => $enrollment->id,
            'assessment_id' => $assessment->id,
        ]);

        $enrollment->refresh();
        $this->assertNotNull($enrollment->final_letter_grade);
    }

    public function test_store_validates_score(): void
    {
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $category   = AssessmentCategory::factory()->create();
        $assessment = Assessment::factory()->create([
            'class_id'               => $classModel->id,
            'assessment_category_id' => $category->id,
        ]);
        $enrollment = Enrollment::factory()->create([
            'class_id' => $classModel->id,
            'status'   => 'enrolled',
        ]);

        $response = $this->actingAs($user)->post(route('admin.grades.store'), [
            'grades' => [
                [
                    'enrollment_id' => $enrollment->id,
                    'assessment_id' => $assessment->id,
                    'score'         => -5,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['grades.0.score']);
    }

    public function test_student_grades_displays_student_data(): void
    {
        $user    = $this->adminUser();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->get(
            route('admin.grades.student', $student)
        );

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Grades/StudentGrades')
            ->has('student')
            ->has('cumulativeGpa')
        );
    }

    public function test_store_upserts_existing_grades(): void
    {
        $this->createDefaultScale();

        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $category   = AssessmentCategory::factory()->create(['weight' => 1.0]);
        $assessment = Assessment::factory()->create([
            'class_id'               => $classModel->id,
            'assessment_category_id' => $category->id,
            'max_score'              => 100,
            'is_extra_credit'        => false,
            'status'                 => 'published',
        ]);

        $student    = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        // Create an initial grade
        Grade::factory()->create([
            'enrollment_id' => $enrollment->id,
            'assessment_id' => $assessment->id,
            'score'         => 70.0,
            'graded_by'     => $user->id,
            'graded_at'     => now(),
        ]);

        // Post a new score for the same enrollment + assessment
        $this->actingAs($user)->post(route('admin.grades.store'), [
            'grades' => [
                [
                    'enrollment_id' => $enrollment->id,
                    'assessment_id' => $assessment->id,
                    'score'         => 85.0,
                    'is_late'       => false,
                ],
            ],
        ]);

        // There should be exactly one grade record, not two
        $this->assertSame(
            1,
            Grade::where('enrollment_id', $enrollment->id)
                ->where('assessment_id', $assessment->id)
                ->count()
        );

        $this->assertDatabaseHas('grades', [
            'enrollment_id' => $enrollment->id,
            'assessment_id' => $assessment->id,
            'score'         => '85.00',
        ]);
    }
}
