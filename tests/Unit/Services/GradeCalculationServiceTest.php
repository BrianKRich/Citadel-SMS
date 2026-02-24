<?php

namespace Tests\Unit\Services;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\ClassModel;
use App\Models\CohortCourse;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\GradingScale;
use App\Models\Student;
use App\Models\User;
use App\Services\GradeCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private GradeCalculationService $service;
    private GradingScale $defaultScale;
    private User $grader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GradeCalculationService();
        $this->grader = User::factory()->create(['role' => 'admin']);

        $this->defaultScale = GradingScale::factory()->create([
            'name' => 'Standard Scale',
            'is_default' => true,
            'scale' => [
                ['letter' => 'A', 'min_percentage' => 90, 'gpa_points' => 4.0],
                ['letter' => 'B', 'min_percentage' => 80, 'gpa_points' => 3.0],
                ['letter' => 'C', 'min_percentage' => 70, 'gpa_points' => 2.0],
                ['letter' => 'D', 'min_percentage' => 60, 'gpa_points' => 1.0],
                ['letter' => 'F', 'min_percentage' => 0,  'gpa_points' => 0.0],
            ],
        ]);
    }

    /**
     * Build a CohortCourse using an auto-created cohort.
     */
    private function makeCohortCourse(array $attrs = []): CohortCourse
    {
        $class  = ClassModel::factory()->create();
        $cohort = $class->cohorts()->where('name', 'alpha')->first();

        return CohortCourse::factory()->create(array_merge(
            ['cohort_id' => $cohort->id],
            $attrs,
        ));
    }

    /**
     * Build a complete enrollment chain: CohortCourse → Enrollment (for a student).
     */
    private function makeEnrollment(CohortCourse $cohortCourse = null, Student $student = null): Enrollment
    {
        $cohortCourse ??= $this->makeCohortCourse();
        $student ??= Student::factory()->create();

        return Enrollment::factory()->create([
            'cohort_course_id' => $cohortCourse->id,
            'student_id' => $student->id,
            'status' => 'enrolled',
        ]);
    }

    /**
     * Create a grade record for a given enrollment and assessment.
     */
    private function makeGrade(Enrollment $enrollment, Assessment $assessment, float $score, bool $isLate = false, float $penalty = null): \App\Models\Grade
    {
        return \App\Models\Grade::factory()->create([
            'enrollment_id' => $enrollment->id,
            'assessment_id' => $assessment->id,
            'score' => $score,
            'is_late' => $isLate,
            'late_penalty' => $penalty,
            'graded_by' => $this->grader->id,
            'graded_at' => now(),
        ]);
    }

    public function test_calculate_weighted_average_single_category(): void
    {
        $cohortCourse = $this->makeCohortCourse();
        $enrollment = $this->makeEnrollment($cohortCourse);

        $category = AssessmentCategory::factory()->create(['weight' => 1.0]);

        // Homework out of 100, student scores 85
        $assessment = Assessment::factory()->create([
            'cohort_course_id' => $cohortCourse->id,
            'assessment_category_id' => $category->id,
            'max_score' => 100,
            'is_extra_credit' => false,
            'status' => 'published',
        ]);

        $this->makeGrade($enrollment, $assessment, 85.0);

        $result = $this->service->calculateWeightedAverage($enrollment->fresh());

        $this->assertEquals(85.0, $result);
    }

    public function test_calculate_weighted_average_multiple_categories(): void
    {
        $cohortCourse = $this->makeCohortCourse();
        $enrollment = $this->makeEnrollment($cohortCourse);

        // Homework: weight 0.4 — student earns 80/100 (80%)
        $hwCategory = AssessmentCategory::factory()->create(['weight' => 0.4]);
        $hwAssessment = Assessment::factory()->create([
            'cohort_course_id' => $cohortCourse->id,
            'assessment_category_id' => $hwCategory->id,
            'max_score' => 100,
            'is_extra_credit' => false,
            'status' => 'published',
        ]);
        $this->makeGrade($enrollment, $hwAssessment, 80.0);

        // Quiz: weight 0.6 — student earns 90/100 (90%)
        $quizCategory = AssessmentCategory::factory()->create(['weight' => 0.6]);
        $quizAssessment = Assessment::factory()->create([
            'cohort_course_id' => $cohortCourse->id,
            'assessment_category_id' => $quizCategory->id,
            'max_score' => 100,
            'is_extra_credit' => false,
            'status' => 'published',
        ]);
        $this->makeGrade($enrollment, $quizAssessment, 90.0);

        $result = $this->service->calculateWeightedAverage($enrollment->fresh());

        // Weighted: (80 * 0.4 + 90 * 0.6) / (0.4 + 0.6) = (32 + 54) / 1.0 = 86.0
        $this->assertEquals(86.0, $result);
    }

    public function test_calculate_weighted_average_with_extra_credit(): void
    {
        $cohortCourse = $this->makeCohortCourse();
        $enrollment = $this->makeEnrollment($cohortCourse);

        // Regular homework: weight 1.0 — student earns 80/100 (80%)
        $category = AssessmentCategory::factory()->create(['weight' => 1.0]);
        $assessment = Assessment::factory()->create([
            'cohort_course_id' => $cohortCourse->id,
            'assessment_category_id' => $category->id,
            'max_score' => 100,
            'is_extra_credit' => false,
            'status' => 'published',
            'weight' => null,
        ]);
        $this->makeGrade($enrollment, $assessment, 80.0);

        // Extra credit: max 10, student earns 10 — weight uses category weight (1.0)
        // extraCreditSum += (10/10) * 1.0 * 100 = 100... but capped at 100
        // To keep the test deterministic, use a small extra credit contribution:
        // extra credit assessment with weight 0.05, student earns 5/10
        // extraCreditSum += (5/10) * 0.05 * 100 = 2.5
        $ecAssessment = Assessment::factory()->create([
            'cohort_course_id' => $cohortCourse->id,
            'assessment_category_id' => $category->id,
            'max_score' => 10,
            'is_extra_credit' => true,
            'status' => 'published',
            'weight' => 0.05,
        ]);
        $this->makeGrade($enrollment, $ecAssessment, 5.0);

        $result = $this->service->calculateWeightedAverage($enrollment->fresh());

        // base average = 80.0, extra credit = (5/10)*0.05*100 = 2.5 → 82.5
        $this->assertEquals(82.5, $result);
    }

    public function test_calculate_weighted_average_with_late_penalty(): void
    {
        $cohortCourse = $this->makeCohortCourse();
        $enrollment = $this->makeEnrollment($cohortCourse);

        $category = AssessmentCategory::factory()->create(['weight' => 1.0]);
        $assessment = Assessment::factory()->create([
            'cohort_course_id' => $cohortCourse->id,
            'assessment_category_id' => $category->id,
            'max_score' => 100,
            'is_extra_credit' => false,
            'status' => 'published',
        ]);

        // Score 100, 10% late penalty → adjusted = 90
        $this->makeGrade($enrollment, $assessment, 100.0, true, 10.0);

        $result = $this->service->calculateWeightedAverage($enrollment->fresh());

        $this->assertEquals(90.0, $result);
    }

    public function test_update_enrollment_grade_sets_letter_and_gpa(): void
    {
        $cohortCourse = $this->makeCohortCourse();
        $enrollment = $this->makeEnrollment($cohortCourse);

        $category = AssessmentCategory::factory()->create(['weight' => 1.0]);
        $assessment = Assessment::factory()->create([
            'cohort_course_id' => $cohortCourse->id,
            'assessment_category_id' => $category->id,
            'max_score' => 100,
            'is_extra_credit' => false,
            'status' => 'published',
        ]);

        // 95/100 → 95% → A → 4.0
        $this->makeGrade($enrollment, $assessment, 95.0);

        $this->service->updateEnrollmentGrade($enrollment->fresh());

        $enrollment->refresh();
        $this->assertSame('A', $enrollment->final_letter_grade);
        $this->assertEquals(4.0, (float) $enrollment->grade_points);
    }

    public function test_calculate_cohort_gpa(): void
    {
        $course1 = Course::factory()->create(['credits' => 3.00]);
        $course2 = Course::factory()->create(['credits' => 3.00]);

        // Both cohort courses share the same cohort
        $class  = ClassModel::factory()->create();
        $cohort = $class->cohorts()->where('name', 'alpha')->first();

        $cc1 = CohortCourse::factory()->create(['cohort_id' => $cohort->id, 'course_id' => $course1->id]);
        $cc2 = CohortCourse::factory()->create(['cohort_id' => $cohort->id, 'course_id' => $course2->id]);

        $student = Student::factory()->create();

        Enrollment::factory()->withGrade('A', 4.0)->create([
            'student_id' => $student->id,
            'cohort_course_id' => $cc1->id,
        ]);

        Enrollment::factory()->withGrade('B', 3.0)->create([
            'student_id' => $student->id,
            'cohort_course_id' => $cc2->id,
        ]);

        $gpa = $this->service->calculateCohortGpa($student->fresh(), $cohort->id);

        // Both courses 3 credits; GPA = (4.0*3 + 3.0*3) / (3+3) = 3.5
        $this->assertEquals(3.5, $gpa);
    }

    public function test_calculate_cumulative_gpa(): void
    {
        $course1 = Course::factory()->create(['credits' => 3.00]);
        $course2 = Course::factory()->create(['credits' => 3.00]);

        $cc1 = $this->makeCohortCourse(['course_id' => $course1->id]);
        $cc2 = $this->makeCohortCourse(['course_id' => $course2->id]);

        $student = Student::factory()->create();

        Enrollment::factory()->withGrade('A', 4.0)->create([
            'student_id' => $student->id,
            'cohort_course_id' => $cc1->id,
        ]);

        Enrollment::factory()->withGrade('C', 2.0)->create([
            'student_id' => $student->id,
            'cohort_course_id' => $cc2->id,
        ]);

        $gpa = $this->service->calculateCumulativeGpa($student->fresh());

        // (4.0*3 + 2.0*3) / (3+3) = 3.0
        $this->assertEquals(3.0, $gpa);
    }
}
