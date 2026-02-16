<?php

namespace Tests\Feature\Admin;

use App\Models\GradingScale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GradingScaleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validScalePayload(array $overrides = []): array
    {
        return array_merge([
            'name'  => 'Standard Scale',
            'scale' => [
                ['letter' => 'A', 'min_percentage' => 90, 'gpa_points' => 4.0],
                ['letter' => 'B', 'min_percentage' => 80, 'gpa_points' => 3.0],
                ['letter' => 'C', 'min_percentage' => 70, 'gpa_points' => 2.0],
                ['letter' => 'D', 'min_percentage' => 60, 'gpa_points' => 1.0],
                ['letter' => 'F', 'min_percentage' => 0,  'gpa_points' => 0.0],
            ],
        ], $overrides);
    }

    public function test_index_displays_scales(): void
    {
        $user = $this->adminUser();
        GradingScale::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('admin.grading-scales.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/GradingScales/Index')
            ->has('scales')
        );
    }

    public function test_store_creates_grading_scale(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(
            route('admin.grading-scales.store'),
            $this->validScalePayload(['name' => 'My Custom Scale'])
        );

        $response->assertRedirect(route('admin.grading-scales.index'));
        $this->assertDatabaseHas('grading_scales', ['name' => 'My Custom Scale']);
    }

    public function test_store_validates_scale_json_structure(): void
    {
        $user = $this->adminUser();

        // Missing required gpa_points in each entry
        $response = $this->actingAs($user)->post(route('admin.grading-scales.store'), [
            'name'  => 'Bad Scale',
            'scale' => [
                ['letter' => 'A', 'min_percentage' => 90],
            ],
        ]);

        $response->assertSessionHasErrors(['scale.0.gpa_points']);
    }

    public function test_set_default_unsets_others(): void
    {
        $user = $this->adminUser();

        $first  = GradingScale::factory()->create(['name' => 'First',  'is_default' => true]);
        $second = GradingScale::factory()->create(['name' => 'Second', 'is_default' => false]);

        $response = $this->actingAs($user)->post(
            route('admin.grading-scales.set-default', $second)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
    }

    public function test_destroy_prevents_deleting_default_scale(): void
    {
        $user  = $this->adminUser();
        $scale = GradingScale::factory()->create(['is_default' => true]);

        $response = $this->actingAs($user)->delete(
            route('admin.grading-scales.destroy', $scale)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('grading_scales', ['id' => $scale->id]);
    }

    public function test_destroy_deletes_non_default_scale(): void
    {
        $user  = $this->adminUser();
        $scale = GradingScale::factory()->create(['is_default' => false]);

        $response = $this->actingAs($user)->delete(
            route('admin.grading-scales.destroy', $scale)
        );

        $response->assertRedirect(route('admin.grading-scales.index'));
        $this->assertDatabaseMissing('grading_scales', ['id' => $scale->id]);
    }
}
