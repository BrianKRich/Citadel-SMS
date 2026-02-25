<?php

namespace Tests\Feature\Admin;

use App\Models\ClassModel;
use App\Models\CohortCourse;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validPayload(array $overrides = []): array
    {
        $dept = Department::factory()->create();
        $role = EmployeeRole::factory()->create(['department_id' => $dept->id]);

        return array_merge([
            'first_name'   => 'Alice',
            'last_name'    => 'Smith',
            'email'        => 'alice.smith@example.com',
            'department_id' => $dept->id,
            'role_id'      => $role->id,
            'hire_date'    => '2023-01-15',
            'status'       => 'active',
        ], $overrides);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.employees.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_employee_list(): void
    {
        Employee::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.employees.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Employees/Index')
                ->has('employees')
                ->has('departments')
            );
    }

    public function test_index_filters_by_search(): void
    {
        Employee::factory()->create(['first_name' => 'Uniquename', 'last_name' => 'Teacher']);
        Employee::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.employees.index', ['search' => 'Uniquename']))
            ->assertInertia(fn (Assert $p) => $p->where('employees.total', 1));
    }

    public function test_index_filters_by_department(): void
    {
        $dept = Department::factory()->create();
        Employee::factory()->create(['department_id' => $dept->id]);
        Employee::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.employees.index', ['department_id' => $dept->id]))
            ->assertInertia(fn (Assert $p) => $p->where('employees.total', 1));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form_with_departments(): void
    {
        Department::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.employees.create'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Employees/Create')
                ->has('departments')
            );
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_employee_and_redirects(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $payload);

        $employee = Employee::first();
        $response->assertRedirect(route('admin.employees.show', $employee));
        $this->assertDatabaseHas('employees', ['first_name' => 'Alice', 'last_name' => 'Smith']);
        $this->assertStringStartsWith('EMP-', $employee->employee_id);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), [])
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'department_id', 'role_id', 'hire_date', 'status']);
    }

    public function test_store_validates_unique_email(): void
    {
        Employee::factory()->create(['email' => 'taken@example.com']);
        $payload = $this->validPayload(['email' => 'taken@example.com']);

        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $payload)
            ->assertSessionHasErrors(['email']);
    }

    public function test_store_validates_status_enum(): void
    {
        $payload = $this->validPayload(['status' => 'retired']);

        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $payload)
            ->assertSessionHasErrors(['status']);
    }

    public function test_store_validates_department_exists(): void
    {
        $payload = $this->validPayload(['department_id' => 99999]);

        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $payload)
            ->assertSessionHasErrors(['department_id']);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_renders_employee_profile(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.employees.show', $employee))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Employees/Show')
                ->has('employee')
            );
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.employees.edit', $employee))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Employees/Edit')
                ->has('employee')
                ->has('departments')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_employee(): void
    {
        $employee = Employee::factory()->create();
        $payload  = $this->validPayload([
            'email'      => $employee->email,
            'first_name' => 'UpdatedFirst',
        ]);
        // Use same department and role
        $payload['department_id'] = $employee->department_id;
        $payload['role_id']       = $employee->role_id;

        $response = $this->actingAs($this->admin())
            ->patch(route('admin.employees.update', $employee), $payload);

        $response->assertRedirect(route('admin.employees.show', $employee));
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'first_name' => 'UpdatedFirst']);
    }

    public function test_update_rejects_duplicate_email(): void
    {
        Employee::factory()->create(['email' => 'other@example.com']);
        $employee = Employee::factory()->create();
        $payload  = $this->validPayload(['email' => 'other@example.com']);
        $payload['department_id'] = $employee->department_id;
        $payload['role_id']       = $employee->role_id;

        $this->actingAs($this->admin())
            ->patch(route('admin.employees.update', $employee), $payload)
            ->assertSessionHasErrors(['email']);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_soft_deletes_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.employees.destroy', $employee));

        $response->assertRedirect(route('admin.employees.index'));
        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
    }

    // ── Trashed ───────────────────────────────────────────────────────────────

    public function test_trashed_index_shows_deleted_employees(): void
    {
        $employee = Employee::factory()->create(['first_name' => 'Deleted']);
        $employee->delete();

        $this->actingAs($this->admin())
            ->get(route('admin.employees.trashed'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Employees/Trashed')
                ->where('employees.total', 1)
            );
    }

    public function test_restore_recovers_soft_deleted_employee(): void
    {
        $employee = Employee::factory()->create();
        $employee->delete();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.employees.restore', $employee->id));

        $response->assertRedirect(route('admin.employees.trashed'));
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'deleted_at' => null]);
    }

    public function test_force_delete_permanently_removes_employee(): void
    {
        $employee = Employee::factory()->create();
        $employee->delete();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.employees.force-delete', $employee->id));

        $response->assertRedirect(route('admin.employees.trashed'));
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    // ── Secondary Role ────────────────────────────────────────────────────────

    public function test_store_accepts_optional_secondary_role_id(): void
    {
        $secondaryRole = EmployeeRole::factory()->create();
        $payload = $this->validPayload(['secondary_role_id' => $secondaryRole->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'email' => $payload['email'],
            'secondary_role_id' => $secondaryRole->id,
        ]);
    }

    public function test_store_allows_null_secondary_role_id(): void
    {
        $payload = $this->validPayload();
        unset($payload['secondary_role_id']);

        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'email' => $payload['email'],
            'secondary_role_id' => null,
        ]);
    }

    public function test_store_validates_secondary_role_id_exists(): void
    {
        $payload = $this->validPayload(['secondary_role_id' => 99999]);

        $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), $payload)
            ->assertSessionHasErrors(['secondary_role_id']);
    }

    public function test_update_secondary_role_does_not_unassign_cohort_courses(): void
    {
        $employee = Employee::factory()->create();

        $class  = ClassModel::factory()->create();
        $cohort = $class->cohorts()->first();
        CohortCourse::factory()->create([
            'cohort_id'   => $cohort->id,
            'employee_id' => $employee->id,
        ]);

        $secondaryRole = EmployeeRole::factory()->create();
        $payload = $this->validPayload([
            'email'             => $employee->email,
            'department_id'     => $employee->department_id,
            'role_id'           => $employee->role_id,
            'secondary_role_id' => $secondaryRole->id,
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.employees.update', $employee), $payload)
            ->assertRedirect();

        // Cohort course assignment must still point to this employee
        $this->assertDatabaseHas('cohort_courses', [
            'cohort_id'   => $cohort->id,
            'employee_id' => $employee->id,
        ]);
    }
}
