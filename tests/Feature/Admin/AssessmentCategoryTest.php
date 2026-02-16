<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AssessmentCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_index_displays_categories(): void
    {
        $user = $this->adminUser();
        AssessmentCategory::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('admin.assessment-categories.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/AssessmentCategories/Index')
            ->has('categories')
        );
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('admin.assessment-categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_store_creates_category(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('admin.assessment-categories.store'), [
            'name'        => 'Homework',
            'weight'      => 0.30,
            'description' => 'Daily homework assignments',
            'course_id'   => null,
        ]);

        $response->assertRedirect(route('admin.assessment-categories.index'));
        $this->assertDatabaseHas('assessment_categories', [
            'name'   => 'Homework',
            'weight' => 0.30,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('admin.assessment-categories.store'), []);

        $response->assertSessionHasErrors(['name', 'weight']);
    }

    public function test_update_modifies_category(): void
    {
        $user = $this->adminUser();
        $category = AssessmentCategory::factory()->create(['name' => 'Old Name', 'weight' => 0.20]);

        $response = $this->actingAs($user)->put(
            route('admin.assessment-categories.update', $category),
            [
                'name'        => 'Updated Name',
                'weight'      => 0.25,
                'description' => null,
                'course_id'   => null,
            ]
        );

        $response->assertRedirect(route('admin.assessment-categories.index'));
        $this->assertDatabaseHas('assessment_categories', [
            'id'   => $category->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_category_without_assessments(): void
    {
        $user = $this->adminUser();
        $category = AssessmentCategory::factory()->create();

        $response = $this->actingAs($user)->delete(
            route('admin.assessment-categories.destroy', $category)
        );

        $response->assertRedirect(route('admin.assessment-categories.index'));
        $this->assertDatabaseMissing('assessment_categories', ['id' => $category->id]);
    }

    public function test_destroy_blocks_category_with_linked_assessments(): void
    {
        $user = $this->adminUser();
        $category = AssessmentCategory::factory()->create();
        Assessment::factory()->create(['assessment_category_id' => $category->id]);

        $response = $this->actingAs($user)->delete(
            route('admin.assessment-categories.destroy', $category)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('assessment_categories', ['id' => $category->id]);
    }
}
