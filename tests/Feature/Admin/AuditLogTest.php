<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function siteAdmin(): User
    {
        return User::factory()->create(['role' => 'site_admin']);
    }

    // -----------------------------------------------------------------------
    // Auth guards
    // -----------------------------------------------------------------------

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.audit-log.index'))->assertRedirect(route('login'));
    }

    public function test_show_requires_auth(): void
    {
        $log = AuditLog::factory()->create();
        $this->get(route('admin.audit-log.show', $log))->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // Purge access control
    // -----------------------------------------------------------------------

    public function test_purge_blocked_for_regular_admin(): void
    {
        $this->actingAs($this->admin())
            ->delete(route('admin.audit-log.purge'), ['older_than' => 'all', 'reason' => 'test'])
            ->assertForbidden();
    }

    public function test_purge_allowed_for_site_admin(): void
    {
        AuditLog::factory()->count(3)->create();

        $this->actingAs($this->siteAdmin())
            ->delete(route('admin.audit-log.purge'), ['older_than' => 'all', 'reason' => 'Clearing test data'])
            ->assertRedirect(route('admin.audit-log.index'));

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_index_shows_can_purge_false_for_regular_admin(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.audit-log.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('canPurge', false)
            );
    }

    public function test_index_shows_can_purge_true_for_site_admin(): void
    {
        $this->actingAs($this->siteAdmin())
            ->get(route('admin.audit-log.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('canPurge', true)
            );
    }

    // -----------------------------------------------------------------------
    // Rendering
    // -----------------------------------------------------------------------

    public function test_index_renders_with_no_logs(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.audit-log.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/AuditLog/Index')
                ->has('logs')
                ->has('filters')
                ->has('users')
            );
    }

    public function test_index_lists_logs(): void
    {
        AuditLog::factory()->count(3)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.audit-log.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/AuditLog/Index')
                ->has('logs.data', 3)
            );
    }

    public function test_show_renders_detail(): void
    {
        $log = AuditLog::factory()->updated()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.audit-log.show', $log))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/AuditLog/Show')
                ->has('log')
                ->where('log.id', $log->id)
            );
    }

    // -----------------------------------------------------------------------
    // Student observer
    // -----------------------------------------------------------------------

    public function test_creating_student_writes_audit_log(): void
    {
        $this->actingAs($this->admin());
        $student = Student::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Student::class,
            'auditable_id'   => $student->id,
            'action'         => 'created',
        ]);
    }

    public function test_updating_student_captures_diff(): void
    {
        $this->actingAs($this->admin());
        $student = Student::factory()->create(['first_name' => 'Alice']);

        $student->update(['first_name' => 'Alicia']);

        $log = AuditLog::where('auditable_type', Student::class)
            ->where('auditable_id', $student->id)
            ->where('action', 'updated')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Alice', $log->old_values['first_name']);
        $this->assertEquals('Alicia', $log->new_values['first_name']);
    }

    public function test_soft_deleting_student_writes_deleted_log(): void
    {
        $this->actingAs($this->admin());
        $student = Student::factory()->create();
        $student->delete();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Student::class,
            'auditable_id'   => $student->id,
            'action'         => 'deleted',
        ]);
    }

    public function test_restoring_student_writes_restored_log(): void
    {
        $this->actingAs($this->admin());
        $student = Student::factory()->create();
        $student->delete();
        $student->restore();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Student::class,
            'auditable_id'   => $student->id,
            'action'         => 'restored',
        ]);
    }

    // -----------------------------------------------------------------------
    // Grade observer
    // -----------------------------------------------------------------------

    public function test_creating_grade_writes_audit_log(): void
    {
        $this->actingAs($this->admin());
        $grade = Grade::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Grade::class,
            'auditable_id'   => $grade->id,
            'action'         => 'created',
        ]);
    }

    public function test_updating_grade_score_captures_old_and_new_values(): void
    {
        $this->actingAs($this->admin());
        $grade = Grade::factory()->create(['score' => '85.00']);

        $grade->update(['score' => '90.00']);

        $log = AuditLog::where('auditable_type', Grade::class)
            ->where('auditable_id', $grade->id)
            ->where('action', 'updated')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('score', $log->old_values);
        $this->assertArrayHasKey('score', $log->new_values);
    }

    // -----------------------------------------------------------------------
    // Employee observer
    // -----------------------------------------------------------------------

    public function test_creating_employee_writes_audit_log(): void
    {
        $this->actingAs($this->admin());
        $employee = Employee::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Employee::class,
            'auditable_id'   => $employee->id,
            'action'         => 'created',
        ]);
    }

    // -----------------------------------------------------------------------
    // Enrollment observer — noise suppression
    // -----------------------------------------------------------------------

    public function test_enrollment_weighted_average_update_does_not_write_log(): void
    {
        $this->actingAs($this->admin());
        $enrollment = Enrollment::factory()->create();

        $logCountBefore = AuditLog::where('auditable_type', Enrollment::class)
            ->where('auditable_id', $enrollment->id)
            ->where('action', 'updated')
            ->count();

        // Simulate GradeCalculationService update (non-tracked fields only)
        $enrollment->update([
            'weighted_average'   => 88.5,
            'final_letter_grade' => 'B+',
        ]);

        $logCountAfter = AuditLog::where('auditable_type', Enrollment::class)
            ->where('auditable_id', $enrollment->id)
            ->where('action', 'updated')
            ->count();

        $this->assertEquals($logCountBefore, $logCountAfter);
    }

    public function test_enrollment_status_change_writes_audit_log(): void
    {
        $this->actingAs($this->admin());
        $enrollment = Enrollment::factory()->create(['status' => 'enrolled']);

        $enrollment->update(['status' => 'dropped']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Enrollment::class,
            'auditable_id'   => $enrollment->id,
            'action'         => 'updated',
        ]);

        $log = AuditLog::where('auditable_type', Enrollment::class)
            ->where('auditable_id', $enrollment->id)
            ->where('action', 'updated')
            ->latest()
            ->first();

        $this->assertEquals('enrolled', $log->old_values['status']);
        $this->assertEquals('dropped', $log->new_values['status']);
    }

    // -----------------------------------------------------------------------
    // Filters
    // -----------------------------------------------------------------------

    public function test_model_type_filter(): void
    {
        AuditLog::factory()->create(['auditable_type' => Student::class]);
        AuditLog::factory()->create(['auditable_type' => Employee::class]);

        $this->actingAs($this->admin())
            ->get(route('admin.audit-log.index', ['model_type' => 'Student']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('logs.data', 1)
            );
    }

    public function test_action_filter(): void
    {
        AuditLog::factory()->created()->create();
        AuditLog::factory()->updated()->create();
        AuditLog::factory()->deleted()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.audit-log.index', ['action' => 'updated']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('logs.data', 1)
            );
    }

    public function test_date_range_filter(): void
    {
        AuditLog::factory()->create(['created_at' => now()->subDays(5)]);
        AuditLog::factory()->create(['created_at' => now()->subDay()]);

        $this->actingAs($this->admin())
            ->get(route('admin.audit-log.index', [
                'date_from' => now()->subDays(2)->toDateString(),
                'date_to'   => now()->toDateString(),
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('logs.data', 1)
            );
    }

    // -----------------------------------------------------------------------
    // Subject label survival
    // -----------------------------------------------------------------------

    public function test_subject_label_preserved_after_student_force_delete(): void
    {
        $this->actingAs($this->admin());
        $student = Student::factory()->create(['first_name' => 'Preserved', 'last_name' => 'Student']);
        $studentId = $student->id;

        $student->delete();
        $student->forceDelete();

        $log = AuditLog::where('auditable_type', Student::class)
            ->where('auditable_id', $studentId)
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->subject_label);
        $this->assertStringContainsString('Preserved', $log->subject_label);
    }
}
