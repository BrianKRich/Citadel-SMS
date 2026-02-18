<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'course_code' => 'MATH-101',
            'name'        => 'Introduction to Algebra',
            'credits'     => 3.0,
            'department'  => 'Mathematics',
            'level'       => 'Beginner',
            'is_active'   => true,
        ], $overrides);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.courses.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_course_list(): void
    {
        Course::factory()->count(3)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.courses.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Courses/Index')
                ->has('courses')
                ->has('departments')
                ->has('levels')
            );
    }

    public function test_index_filters_by_search(): void
    {
        Course::factory()->create(['name' => 'Quantum Physics Special', 'course_code' => 'SCI-999']);
        Course::factory()->count(2)->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.courses.index', ['search' => 'Quantum']));

        $response->assertInertia(fn (Assert $p) => $p
            ->where('courses.total', 1)
        );
    }

    public function test_index_filters_by_department(): void
    {
        Course::factory()->create(['department' => 'History']);
        Course::factory()->count(2)->create(['department' => 'Mathematics']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.courses.index', ['department' => 'History']));

        $response->assertInertia(fn (Assert $p) => $p
            ->where('courses.total', 1)
        );
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.courses.create'))
            ->assertInertia(fn (Assert $p) => $p->component('Admin/Courses/Create'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_course_and_redirects(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), $this->validPayload());

        $course = Course::first();
        $response->assertRedirect(route('admin.courses.show', $course));
        $this->assertDatabaseHas('courses', ['course_code' => 'MATH-101']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), [])
            ->assertSessionHasErrors(['course_code', 'name']);
    }

    public function test_store_validates_unique_course_code(): void
    {
        Course::factory()->create(['course_code' => 'MATH-101']);

        $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), $this->validPayload())
            ->assertSessionHasErrors(['course_code']);
    }

    public function test_store_validates_credits_range(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), $this->validPayload(['credits' => -1]))
            ->assertSessionHasErrors(['credits']);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_renders_course_detail(): void
    {
        $course = Course::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.courses.show', $course))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Courses/Show')
                ->has('course')
            );
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form(): void
    {
        $course = Course::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.courses.edit', $course))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Courses/Edit')
                ->has('course')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_course(): void
    {
        $course = Course::factory()->create(['course_code' => 'OLD-100']);

        $response = $this->actingAs($this->admin())
            ->patch(route('admin.courses.update', $course), $this->validPayload([
                'course_code' => 'OLD-100',
                'name'        => 'Renamed Course',
            ]));

        $response->assertRedirect(route('admin.courses.show', $course));
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'Renamed Course']);
    }

    public function test_update_allows_same_course_code_for_same_course(): void
    {
        $course = Course::factory()->create(['course_code' => 'SAME-999']);

        $this->actingAs($this->admin())
            ->patch(route('admin.courses.update', $course), $this->validPayload(['course_code' => 'SAME-999']))
            ->assertSessionHasNoErrors();
    }

    public function test_update_rejects_duplicate_course_code(): void
    {
        Course::factory()->create(['course_code' => 'TAKEN-1']);
        $course = Course::factory()->create(['course_code' => 'MINE-2']);

        $this->actingAs($this->admin())
            ->patch(route('admin.courses.update', $course), $this->validPayload(['course_code' => 'TAKEN-1']))
            ->assertSessionHasErrors(['course_code']);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.courses.destroy', $course));

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
