<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'       => '2024-2025',
            'start_date' => '2024-08-01',
            'end_date'   => '2025-05-31',
            'is_current' => false,
        ], $overrides);
    }

    private function validTermPayload(array $overrides = []): array
    {
        return array_merge([
            'name'       => 'Fall 2024',
            'start_date' => '2024-08-01',
            'end_date'   => '2024-12-15',
            'is_current' => false,
        ], $overrides);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.academic-years.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_academic_year_list(): void
    {
        AcademicYear::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.academic-years.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/AcademicYears/Index')
                ->has('academic_years')
            );
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.academic-years.create'))
            ->assertInertia(fn (Assert $p) => $p->component('Admin/AcademicYears/Create'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_academic_year_and_redirects(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.academic-years.store'), $this->validPayload());

        $year = AcademicYear::first();
        $response->assertRedirect(route('admin.academic-years.show', $year));
        $this->assertDatabaseHas('academic_years', ['name' => '2024-2025']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.academic-years.store'), [])
            ->assertSessionHasErrors(['name', 'start_date', 'end_date']);
    }

    public function test_store_validates_end_date_after_start_date(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.academic-years.store'), $this->validPayload([
                'start_date' => '2025-01-01',
                'end_date'   => '2024-01-01',
            ]))
            ->assertSessionHasErrors(['end_date']);
    }

    public function test_store_creates_with_inline_terms(): void
    {
        $payload = $this->validPayload([
            'terms' => [[
                'name'       => 'Fall 2024',
                'start_date' => '2024-08-01',
                'end_date'   => '2024-12-15',
                'is_current' => false,
            ]],
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.academic-years.store'), $payload);

        $year = AcademicYear::first();
        $this->assertCount(1, $year->terms);
        $this->assertDatabaseHas('terms', ['name' => 'Fall 2024']);
    }

    public function test_store_setting_as_current_unsets_other_years(): void
    {
        $other = AcademicYear::factory()->create(['is_current' => true]);

        $this->actingAs($this->admin())
            ->post(route('admin.academic-years.store'), $this->validPayload([
                'name'       => 'UNIQUE-TEST-YEAR',
                'is_current' => true,
            ]));

        $other->refresh();
        $this->assertFalse($other->is_current);
        $newYear = AcademicYear::where('name', 'UNIQUE-TEST-YEAR')->first();
        $this->assertNotNull($newYear);
        $this->assertTrue($newYear->is_current);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_renders_academic_year(): void
    {
        $year = AcademicYear::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.academic-years.show', $year))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/AcademicYears/Show')
                ->has('academicYear')
            );
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form(): void
    {
        $year = AcademicYear::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.academic-years.edit', $year))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/AcademicYears/Edit')
                ->has('academicYear')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_academic_year(): void
    {
        $year = AcademicYear::factory()->create();

        $response = $this->actingAs($this->admin())
            ->patch(route('admin.academic-years.update', $year), $this->validPayload([
                'name' => 'Updated Year',
            ]));

        $response->assertRedirect(route('admin.academic-years.show', $year));
        $this->assertDatabaseHas('academic_years', ['id' => $year->id, 'name' => 'Updated Year']);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_academic_year(): void
    {
        $year = AcademicYear::factory()->create();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.academic-years.destroy', $year));

        $response->assertRedirect(route('admin.academic-years.index'));
        $this->assertDatabaseMissing('academic_years', ['id' => $year->id]);
    }

    // ── Set Current ───────────────────────────────────────────────────────────

    public function test_set_current_marks_year_as_current(): void
    {
        $first  = AcademicYear::factory()->create(['is_current' => true]);
        $second = AcademicYear::factory()->create(['is_current' => false]);

        $this->actingAs($this->admin())
            ->post(route('admin.academic-years.set-current', $second))
            ->assertRedirect();

        $first->refresh();
        $second->refresh();
        $this->assertFalse($first->is_current);
        $this->assertTrue($second->is_current);
    }

    // ── Term Management ───────────────────────────────────────────────────────

    public function test_store_term_creates_term_under_year(): void
    {
        $year = AcademicYear::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.academic-years.terms.store', $year), $this->validTermPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('terms', [
            'academic_year_id' => $year->id,
            'name'             => 'Fall 2024',
        ]);
    }

    public function test_store_term_validates_required_fields(): void
    {
        $year = AcademicYear::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.academic-years.terms.store', $year), [])
            ->assertSessionHasErrors(['name', 'start_date', 'end_date']);
    }

    public function test_update_term_modifies_term(): void
    {
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->for($year)->create(['name' => 'Old Term']);

        $this->actingAs($this->admin())
            ->put(route('admin.academic-years.terms.update', [$year, $term]),
                $this->validTermPayload(['name' => 'New Term']))
            ->assertRedirect();

        $this->assertDatabaseHas('terms', ['id' => $term->id, 'name' => 'New Term']);
    }

    public function test_destroy_term_deletes_term(): void
    {
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->for($year)->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.academic-years.terms.destroy', [$year, $term]))
            ->assertRedirect();

        $this->assertDatabaseMissing('terms', ['id' => $term->id]);
    }

    public function test_set_current_term_marks_term_as_current(): void
    {
        $year   = AcademicYear::factory()->create();
        $first  = Term::factory()->for($year)->create(['is_current' => true]);
        $second = Term::factory()->for($year)->create(['is_current' => false]);

        $this->actingAs($this->admin())
            ->post(route('admin.academic-years.terms.set-current', [$year, $second]))
            ->assertRedirect();

        $first->refresh();
        $second->refresh();
        $this->assertFalse($first->is_current);
        $this->assertTrue($second->is_current);
    }
}
