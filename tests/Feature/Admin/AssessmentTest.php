<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\ClassModel;
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

    public function test_index_displays_assessments(): void
    {
        $user = $this->adminUser();
        Assessment::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('admin.assessments.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Assessments/Index')
            ->has('assessments')
        );
    }

    public function test_index_filters_by_class(): void
    {
        $user = $this->adminUser();
        $targetClass = ClassModel::factory()->create();
        $otherClass  = ClassModel::factory()->create();

        $targetAssessment = Assessment::factory()->create(['class_id' => $targetClass->id, 'name' => 'Target Quiz']);
        Assessment::factory()->create(['class_id' => $otherClass->id, 'name' => 'Other Quiz']);

        $response = $this->actingAs($user)->get(
            route('admin.assessments.index', ['class_id' => $targetClass->id])
        );

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Assessments/Index')
            ->where('filters.class_id', (string) $targetClass->id)
        );
    }

    public function test_index_filters_by_status(): void
    {
        $user = $this->adminUser();
        Assessment::factory()->create(['status' => 'published']);
        Assessment::factory()->draft()->create();

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
        $class    = ClassModel::factory()->create();
        $category = AssessmentCategory::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.assessments.store'), [
            'class_id'               => $class->id,
            'assessment_category_id' => $category->id,
            'name'                   => 'Midterm Exam',
            'max_score'              => 100,
            'due_date'               => '2026-03-15',
            'is_extra_credit'        => false,
            'status'                 => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assessments', [
            'name'     => 'Midterm Exam',
            'class_id' => $class->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('admin.assessments.store'), []);

        $response->assertSessionHasErrors(['class_id', 'assessment_category_id', 'name', 'max_score', 'status']);
    }

    public function test_show_displays_assessment_with_grade_stats(): void
    {
        $user       = $this->adminUser();
        $class      = ClassModel::factory()->create();
        $category   = AssessmentCategory::factory()->create();
        $assessment = Assessment::factory()->create([
            'class_id'               => $class->id,
            'assessment_category_id' => $category->id,
            'status'                 => 'published',
        ]);

        $enrollment = Enrollment::factory()->create([
            'class_id' => $class->id,
            'status'   => 'enrolled',
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
        $class      = ClassModel::factory()->create();
        $category   = AssessmentCategory::factory()->create();
        $assessment = Assessment::factory()->create([
            'class_id'               => $class->id,
            'assessment_category_id' => $category->id,
            'name'                   => 'Original Name',
            'status'                 => 'published',
        ]);

        $response = $this->actingAs($user)->put(route('admin.assessments.update', $assessment), [
            'class_id'               => $class->id,
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
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.assessments.destroy', $assessment));

        $response->assertRedirect(route('admin.assessments.index'));
        $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]);
    }
}
