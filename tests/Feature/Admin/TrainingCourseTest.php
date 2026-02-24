<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\TrainingCourse;
use App\Models\TrainingRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrainingCourseTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function enableTraining(): void
    {
        Setting::set('feature_staff_training_enabled', '1', 'boolean');
    }

    // -----------------------------------------------------------------------
    // 1. All routes blocked when feature disabled
    // -----------------------------------------------------------------------

    public function test_all_routes_blocked_when_feature_disabled(): void
    {
        $admin  = $this->adminUser();
        $course = TrainingCourse::factory()->create();

        $this->actingAs($admin)->get(route('admin.training-courses.index'))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.training-courses.create'))->assertStatus(403);
        $this->actingAs($admin)->post(route('admin.training-courses.store'))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.training-courses.edit', $course))->assertStatus(403);
        $this->actingAs($admin)->put(route('admin.training-courses.update', $course))->assertStatus(403);
        $this->actingAs($admin)->delete(route('admin.training-courses.destroy', $course))->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // 2. Feature flag shared
    // -----------------------------------------------------------------------

    public function test_feature_flag_shared_as_false_by_default(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) =>
                $page->where('features.staff_training_enabled', false)
            );
    }

    public function test_feature_flag_shared_as_true_when_enabled(): void
    {
        $this->enableTraining();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) =>
                $page->where('features.staff_training_enabled', true)
            );
    }

    // -----------------------------------------------------------------------
    // 3. Toggle via feature settings
    // -----------------------------------------------------------------------

    public function test_site_admin_can_enable_staff_training(): void
    {
        $siteAdmin = User::factory()->create(['role' => 'site_admin']);

        $this->actingAs($siteAdmin)
            ->post(route('admin.feature-settings.update'), ['staff_training_enabled' => true])
            ->assertRedirect();

        $this->assertSame('1', Setting::get('feature_staff_training_enabled'));
    }

    public function test_site_admin_can_disable_staff_training(): void
    {
        $this->enableTraining();
        $siteAdmin = User::factory()->create(['role' => 'site_admin']);

        $this->actingAs($siteAdmin)
            ->post(route('admin.feature-settings.update'), ['staff_training_enabled' => false])
            ->assertRedirect();

        $this->assertSame('0', Setting::get('feature_staff_training_enabled'));
    }

    // -----------------------------------------------------------------------
    // 4. Index renders
    // -----------------------------------------------------------------------

    public function test_index_renders_with_courses(): void
    {
        $this->enableTraining();
        $admin = $this->adminUser();
        TrainingCourse::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.training-courses.index'))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Courses/Index')
                     ->has('courses.data', 3)
            );
    }

    // -----------------------------------------------------------------------
    // 5. Create/Store
    // -----------------------------------------------------------------------

    public function test_create_renders(): void
    {
        $this->enableTraining();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.training-courses.create'))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Courses/Create')
            );
    }

    public function test_can_create_course(): void
    {
        $this->enableTraining();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.training-courses.store'), [
                'name'        => 'Fire Safety',
                'trainer'     => 'Jane Smith',
                'description' => 'Annual fire safety training',
                'is_active'   => true,
            ])
            ->assertRedirect(route('admin.training-courses.index'));

        $this->assertDatabaseHas('training_courses', [
            'name'      => 'Fire Safety',
            'trainer'   => 'Jane Smith',
            'is_active' => true,
        ]);
    }

    public function test_store_requires_name_and_trainer(): void
    {
        $this->enableTraining();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.training-courses.store'), [])
            ->assertSessionHasErrors(['name', 'trainer']);
    }

    // -----------------------------------------------------------------------
    // 6. Edit/Update
    // -----------------------------------------------------------------------

    public function test_edit_renders(): void
    {
        $this->enableTraining();
        $admin  = $this->adminUser();
        $course = TrainingCourse::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.training-courses.edit', $course))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Training/Courses/Edit')
                     ->has('course')
            );
    }

    public function test_can_update_course(): void
    {
        $this->enableTraining();
        $admin  = $this->adminUser();
        $course = TrainingCourse::factory()->create(['name' => 'Old Name']);

        $this->actingAs($admin)
            ->put(route('admin.training-courses.update', $course), [
                'name'      => 'New Name',
                'trainer'   => 'Bob Jones',
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.training-courses.index'));

        $this->assertDatabaseHas('training_courses', [
            'id'        => $course->id,
            'name'      => 'New Name',
            'trainer'   => 'Bob Jones',
            'is_active' => false,
        ]);
    }

    // -----------------------------------------------------------------------
    // 7. Destroy
    // -----------------------------------------------------------------------

    public function test_can_delete_course_with_no_records(): void
    {
        $this->enableTraining();
        $admin  = $this->adminUser();
        $course = TrainingCourse::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.training-courses.destroy', $course))
            ->assertRedirect(route('admin.training-courses.index'));

        $this->assertDatabaseMissing('training_courses', ['id' => $course->id]);
    }

    public function test_cannot_delete_course_with_existing_records(): void
    {
        $this->enableTraining();
        $admin  = $this->adminUser();
        $record = TrainingRecord::factory()->create();
        $course = $record->trainingCourse;

        $this->actingAs($admin)
            ->delete(route('admin.training-courses.destroy', $course))
            ->assertRedirect();

        $this->assertDatabaseHas('training_courses', ['id' => $course->id]);
    }

    // -----------------------------------------------------------------------
    // 8. Non-admin blocked
    // -----------------------------------------------------------------------

    public function test_non_admin_blocked_from_index(): void
    {
        $this->enableTraining();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.training-courses.index'))
            ->assertStatus(403);
    }
}
