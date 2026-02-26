<?php

namespace Tests\Feature\Admin;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GuardianTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** Create a guardian directly (no GuardianFactory exists). */
    private function makeGuardian(array $overrides = []): Guardian
    {
        return Guardian::create(array_merge([
            'first_name'   => 'John',
            'last_name'    => 'Parent',
            'relationship' => 'father',
            'email'        => 'john.parent@example.com',
        ], $overrides));
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name'   => 'Mary',
            'last_name'    => 'Guardian',
            'relationship' => 'mother',
            'email'        => 'mary.guardian@example.com',
            'phone'        => '5550001',
        ], $overrides);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.guardians.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_guardian_list(): void
    {
        $this->makeGuardian();

        $this->actingAs($this->admin())
            ->get(route('admin.guardians.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Guardians/Index')
                ->has('guardians')
            );
    }

    public function test_index_filters_by_search(): void
    {
        $this->makeGuardian(['first_name' => 'Zephyr', 'last_name' => 'Unique', 'email' => 'z@example.com']);
        $this->makeGuardian(['first_name' => 'Other', 'last_name' => 'Person', 'email' => 'o@example.com']);

        $this->actingAs($this->admin())
            ->get(route('admin.guardians.index', ['search' => 'Zephyr']))
            ->assertInertia(fn (Assert $p) => $p->where('guardians.total', 1));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form_with_students(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.guardians.create'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Guardians/Create')
                ->has('preselectedStudentId')
                ->has('preselectedStudent')
            );
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_guardian_and_redirects(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.guardians.store'), $this->validPayload());

        $guardian = Guardian::first();
        $response->assertRedirect(route('admin.guardians.show', $guardian));
        $this->assertDatabaseHas('guardians', ['first_name' => 'Mary', 'last_name' => 'Guardian']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.guardians.store'), [])
            ->assertSessionHasErrors(['first_name', 'last_name', 'relationship', 'phone']);
    }

    public function test_store_validates_relationship_enum(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.guardians.store'), $this->validPayload(['relationship' => 'uncle']))
            ->assertSessionHasErrors(['relationship']);
    }

    public function test_store_validates_phone_digits(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.guardians.store'), $this->validPayload(['phone' => '12345']))
            ->assertSessionHasErrors(['phone']);
    }

    public function test_store_attaches_students(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.guardians.store'), $this->validPayload([
                'students' => [['id' => $student->id, 'is_primary' => true]],
            ]));

        $guardian = Guardian::first();
        $this->assertTrue($guardian->students->contains($student));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_renders_guardian_profile(): void
    {
        $guardian = $this->makeGuardian();

        $this->actingAs($this->admin())
            ->get(route('admin.guardians.show', $guardian))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Guardians/Show')
                ->has('guardian')
            );
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form(): void
    {
        $guardian = $this->makeGuardian();

        $this->actingAs($this->admin())
            ->get(route('admin.guardians.edit', $guardian))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Guardians/Edit')
                ->has('guardian')
                ->has('students')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_guardian(): void
    {
        $guardian = $this->makeGuardian();

        $response = $this->actingAs($this->admin())
            ->patch(route('admin.guardians.update', $guardian), $this->validPayload([
                'last_name' => 'NewLastName',
            ]));

        $response->assertRedirect(route('admin.guardians.show', $guardian));
        $this->assertDatabaseHas('guardians', ['id' => $guardian->id, 'last_name' => 'NewLastName']);
    }

    public function test_update_syncs_student_relationships(): void
    {
        $guardian = $this->makeGuardian();
        $student  = Student::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.guardians.update', $guardian), $this->validPayload([
                'students' => [['id' => $student->id, 'is_primary' => false]],
            ]));

        $guardian->refresh();
        $this->assertTrue($guardian->students->contains($student));
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_guardian(): void
    {
        $guardian = $this->makeGuardian();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.guardians.destroy', $guardian));

        $response->assertRedirect(route('admin.guardians.index'));
        $this->assertDatabaseMissing('guardians', ['id' => $guardian->id]);
    }
}
