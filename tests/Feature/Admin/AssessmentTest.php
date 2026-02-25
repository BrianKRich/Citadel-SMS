<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\ClassModel;
use App\Models\ClassCourse;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AssessmentTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeClassCourse(array $overrides = []): ClassCourse
    {
        $class = ClassModel::factory()->create();

        return ClassCourse::factory()->create(array_merge(
            ['class_id' => $class->id],
            $overrides
        ));
    }

    public function test_index_displays_assessments(): void
    {
        $user = $this->adminUser();
        $cc   = $this->makeClassCourse();
        Assessment::factory()->count(3)->create(['class_course_id' => $cc->id]);

        $response = $this->actingAs($user)->get(route('admin.assessments.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Assessments/Index')
            ->has('assessments')
        );
    }

    public function test_index_filters_by_class_course(): void
    {
        $user     = $this->adminUser();
        $targetCc = $this->makeClassCourse();
        $otherCc  = $this->makeClassCourse();

        Assessment::factory()->create(['class_course_id' => $targetCc->id, 'name' => 'Target Quiz']);
        Assessment::factory()->create(['class_course_id' => $otherCc->id, 'name' => 'Other Quiz']);

        $response = $this->actingAs($user)->get(
            route('admin.assessments.index', ['class_course_id' => $targetCc->id])
        );

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Assessments/Index')
            ->where('filters.class_course_id', (string) $targetCc->id)
        );
    }

    public function test_index_filters_by_status(): void
    {
        $user = $this->adminUser();
        $cc   = $this->makeClassCourse();
        Assessment::factory()->create(['class_course_id' => $cc->id, 'status' => 'published']);
        Assessment::factory()->draft()->create(['class_course_id' => $cc->id]);

        $response = $this->actingAs($user)->get(
            route('admin.assessments.index', ['status' => 'draft'])
        );

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Assessments/Index')
            ->where('filters.status', 'draft')
        );
    }

    public function test_store_creates_assessment(): void
    {
        $user     = $this->adminUser();
        $cc       = $this->makeClassCourse();
        $category = AssessmentCategory::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.assessments.store'), [
            'class_course_id'       => $cc->id,
            'assessment_category_id' => $category->id,
            'name'                   => 'Midterm Exam',
            'max_score'              => 100,
            'due_date'               => '2026-03-15',
            'is_extra_credit'        => false,
            'status'                 => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assessments', [
            'name'             => 'Midterm Exam',
            'class_course_id' => $cc->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('admin.assessments.store'), []);

        $response->assertSessionHasErrors([
            'class_course_id', 'assessment_category_id', 'name', 'max_score', 'status',
        ]);
    }

    public function test_show_displays_assessment_with_grade_stats(): void
    {
        $user       = $this->adminUser();
        $cc         = $this->makeClassCourse();
        $category   = AssessmentCategory::factory()->create();
        $assessment = Assessment::factory()->create([
            'class_course_id'       => $cc->id,
            'assessment_category_id' => $category->id,
            'status'                 => 'published',
        ]);

        $enrollment = Enrollment::factory()->create([
            'class_course_id' => $cc->id,
            'status'           => 'enrolled',
        ]);

        Grade::factory()->create([
            'assessment_id' => $assessment->id,
            'enrollment_id' => $enrollment->id,
            'score'         => 85.0,
            'graded_by'     => $user->id,
            'graded_at'     => now(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.assessments.show', $assessment));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Assessments/Show')
            ->has('assessment')
            ->has('gradeStats')
        );
    }

    public function test_update_modifies_assessment(): void
    {
        $user       = $this->adminUser();
        $cc         = $this->makeClassCourse();
        $category   = AssessmentCategory::factory()->create();
        $assessment = Assessment::factory()->create([
            'class_course_id'       => $cc->id,
            'assessment_category_id' => $category->id,
            'name'                   => 'Original Name',
            'status'                 => 'published',
        ]);

        $response = $this->actingAs($user)->put(route('admin.assessments.update', $assessment), [
            'class_course_id'       => $cc->id,
            'assessment_category_id' => $category->id,
            'name'                   => 'Updated Name',
            'max_score'              => 100,
            'is_extra_credit'        => false,
            'status'                 => 'published',
        ]);

        $response->assertRedirect(route('admin.assessments.show', $assessment));
        $this->assertDatabaseHas('assessments', [
            'id'   => $assessment->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_assessment(): void
    {
        $user       = $this->adminUser();
        $cc         = $this->makeClassCourse();
        $assessment = Assessment::factory()->create(['class_course_id' => $cc->id]);

        $response = $this->actingAs($user)->delete(route('admin.assessments.destroy', $assessment));

        $response->assertRedirect(route('admin.assessments.index'));
        $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]);
    }
}
