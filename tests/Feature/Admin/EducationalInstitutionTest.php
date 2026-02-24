<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\CohortCourse;
use App\Models\EducationalInstitution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EducationalInstitutionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'           => 'Test Technical College',
            'type'           => 'technical_college',
            'address'        => '123 College Ave, Atlanta, GA 30001',
            'phone'          => '555-555-0100',
            'contact_person' => 'Jane Smith',
        ], $overrides);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.institutions.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_institution_list(): void
    {
        EducationalInstitution::factory()->count(3)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.institutions.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Institutions/Index')
                ->has('institutions')
                ->has('filters')
            );
    }

    public function test_index_paginates_results(): void
    {
        EducationalInstitution::factory()->count(15)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.institutions.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->where('institutions.per_page', 10)
                ->where('institutions.total', 15)
            );
    }

    public function test_index_filters_by_type(): void
    {
        EducationalInstitution::factory()->create(['type' => 'technical_college']);
        EducationalInstitution::factory()->create(['type' => 'university']);

        $this->actingAs($this->admin())
            ->get(route('admin.institutions.index', ['type' => 'university']))
            ->assertInertia(fn (Assert $p) => $p
                ->where('institutions.total', 1)
            );
    }

    public function test_index_search_filters_by_name(): void
    {
        EducationalInstitution::factory()->create(['name' => 'Georgia Tech College']);
        EducationalInstitution::factory()->create(['name' => 'Kennesaw State University']);

        $this->actingAs($this->admin())
            ->get(route('admin.institutions.index', ['search' => 'Georgia']))
            ->assertInertia(fn (Assert $p) => $p
                ->where('institutions.total', 1)
            );
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.institutions.create'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Institutions/Create')
            );
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_institution_and_redirects(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.institutions.store'), $this->validPayload());

        $response->assertRedirect(route('admin.institutions.index'));
        $this->assertDatabaseHas('educational_institutions', [
            'name' => 'Test Technical College',
            'type' => 'technical_college',
        ]);
    }

    public function test_store_creates_university(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.institutions.store'), $this->validPayload([
                'name' => 'State University',
                'type' => 'university',
            ]));

        $this->assertDatabaseHas('educational_institutions', [
            'name' => 'State University',
            'type' => 'university',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.institutions.store'), [])
            ->assertSessionHasErrors(['name', 'type']);
    }

    public function test_store_validates_type_enum(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.institutions.store'), $this->validPayload([
                'type' => 'community_college',
            ]))
            ->assertSessionHasErrors(['type']);
    }

    public function test_store_optional_fields_are_nullable(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.institutions.store'), [
                'name' => 'Minimal College',
                'type' => 'technical_college',
            ]);

        $response->assertRedirect(route('admin.institutions.index'));
        $this->assertDatabaseHas('educational_institutions', [
            'name'           => 'Minimal College',
            'address'        => null,
            'phone'          => null,
            'contact_person' => null,
        ]);
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form(): void
    {
        $institution = EducationalInstitution::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.institutions.edit', $institution))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Institutions/Edit')
                ->has('institution')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_institution(): void
    {
        $institution = EducationalInstitution::factory()->create([
            'name' => 'Old Name',
            'type' => 'technical_college',
        ]);

        $response = $this->actingAs($this->admin())
            ->put(route('admin.institutions.update', $institution), $this->validPayload([
                'name' => 'New Name',
                'type' => 'university',
            ]));

        $response->assertRedirect(route('admin.institutions.index'));
        $this->assertDatabaseHas('educational_institutions', [
            'id'   => $institution->id,
            'name' => 'New Name',
            'type' => 'university',
        ]);
    }

    public function test_update_validates_required_fields(): void
    {
        $institution = EducationalInstitution::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.institutions.update', $institution), [])
            ->assertSessionHasErrors(['name', 'type']);
    }

    public function test_update_validates_type_enum(): void
    {
        $institution = EducationalInstitution::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.institutions.update', $institution), $this->validPayload([
                'type' => 'invalid',
            ]))
            ->assertSessionHasErrors(['type']);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_institution(): void
    {
        $institution = EducationalInstitution::factory()->create();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.institutions.destroy', $institution));

        $response->assertRedirect(route('admin.institutions.index'));
        $this->assertDatabaseMissing('educational_institutions', ['id' => $institution->id]);
    }

    public function test_destroy_blocked_when_cohort_courses_reference_it(): void
    {
        $institution = EducationalInstitution::factory()->create(['type' => 'technical_college']);
        $class       = ClassModel::factory()->create();
        $cohort      = $class->cohorts()->first();
        CohortCourse::factory()->create([
            'cohort_id'       => $cohort->id,
            'instructor_type' => 'technical_college',
            'institution_id'  => $institution->id,
            'employee_id'     => null,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.institutions.destroy', $institution))
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('educational_institutions', ['id' => $institution->id]);
    }

    public function test_destroy_blocked_for_university_type_with_courses(): void
    {
        $institution = EducationalInstitution::factory()->create(['type' => 'university']);
        $class       = ClassModel::factory()->create();
        $cohort      = $class->cohorts()->first();
        CohortCourse::factory()->create([
            'cohort_id'       => $cohort->id,
            'instructor_type' => 'university',
            'institution_id'  => $institution->id,
            'employee_id'     => null,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.institutions.destroy', $institution))
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('educational_institutions', ['id' => $institution->id]);
    }
}
