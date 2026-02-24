<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function siteAdmin(): User
    {
        return User::factory()->create(['role' => 'site_admin']);
    }

    private function enableAttendance(): void
    {
        Setting::set('feature_attendance_enabled', '1', 'boolean');
    }

    // -----------------------------------------------------------------------
    // Feature flag tests
    // -----------------------------------------------------------------------

    public function test_routes_blocked_when_attendance_disabled(): void
    {
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();

        $this->actingAs($user)->get(route('admin.attendance.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.attendance.take', $classModel))->assertStatus(403);
        $this->actingAs($user)->post(route('admin.attendance.store'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.attendance.student', $student))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.attendance.summary', $classModel))->assertStatus(403);
    }

    public function test_routes_accessible_when_attendance_enabled(): void
    {
        $this->enableAttendance();
        $user    = $this->adminUser();

        $response = $this->actingAs($user)->get(route('admin.attendance.index'));
        $response->assertOk();
    }

    public function test_toggle_enables_attendance(): void
    {
        $user = $this->siteAdmin();

        $this->assertDatabaseMissing('settings', [
            'key'   => 'feature_attendance_enabled',
            'value' => '1',
        ]);

        $this->actingAs($user)->post(route('admin.feature-settings.update'), [
            'attendance_enabled' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key'   => 'feature_attendance_enabled',
            'value' => '1',
        ]);
    }

    public function test_toggle_disables_attendance(): void
    {
        $this->enableAttendance();
        $user = $this->siteAdmin();

        $this->actingAs($user)->post(route('admin.feature-settings.update'), [
            'attendance_enabled' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key'   => 'feature_attendance_enabled',
            'value' => '0',
        ]);
    }

    public function test_features_prop_shared_correctly(): void
    {
        $user = $this->adminUser();

        // Disabled by default
        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('features.attendance_enabled', false)
            );

        // Enabled after toggle
        $this->enableAttendance();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('features.attendance_enabled', true)
            );
    }

    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    public function test_index_requires_auth(): void
    {
        $this->enableAttendance();
        $this->get(route('admin.attendance.index'))->assertRedirect(route('login'));
    }

    public function test_index_renders_class_list(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        ClassModel::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.attendance.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attendance/Index')
                ->has('classes')
                ->has('filters')
            );
    }

    // -----------------------------------------------------------------------
    // Take
    // -----------------------------------------------------------------------

    public function test_take_renders_enrolled_students(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();

        Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.take', $classModel))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attendance/Take')
                ->has('classModel')
                ->has('enrollments', 1)
                ->has('existingRecords')
                ->has('date')
            );
    }

    public function test_take_prefills_existing_records(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();

        Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        $date = today()->toDateString();

        AttendanceRecord::factory()->create([
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
            'date'       => $date,
            'status'     => 'absent',
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.take', ['classModel' => $classModel->id, 'date' => $date]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attendance/Take')
                ->where("existingRecords.{$student->id}.status", 'absent')
            );
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    public function test_store_creates_attendance_records(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();

        Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        $date = today()->toDateString();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_id' => $classModel->id,
            'date'     => $date,
            'records'  => [
                ['student_id' => $student->id, 'status' => 'present', 'notes' => null],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
            'date'       => $date,
            'status'     => 'present',
        ]);
    }

    public function test_store_upserts_same_date(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();
        $date       = today()->toDateString();

        AttendanceRecord::factory()->create([
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
            'date'       => $date,
            'status'     => 'absent',
        ]);

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_id' => $classModel->id,
            'date'     => $date,
            'records'  => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ]);

        $this->assertSame(
            1,
            AttendanceRecord::where('student_id', $student->id)
                ->where('class_id', $classModel->id)
                ->whereDate('date', $date)
                ->count()
        );

        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
            'status'     => 'present',
        ]);
    }

    public function test_store_rejects_invalid_status(): void
    {
        $this->enableAttendance();
        $user    = $this->adminUser();
        $class   = ClassModel::factory()->create();
        $student = Student::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_id' => $class->id,
            'date'     => today()->toDateString(),
            'records'  => [
                ['student_id' => $student->id, 'status' => 'tardy'],
            ],
        ])->assertSessionHasErrors(['records.0.status']);
    }

    // -----------------------------------------------------------------------
    // Student history
    // -----------------------------------------------------------------------

    public function test_student_history_renders_correctly(): void
    {
        $this->enableAttendance();
        $user    = $this->adminUser();
        $student = Student::factory()->create();

        AttendanceRecord::factory()->create(['student_id' => $student->id]);

        $this->actingAs($user)
            ->get(route('admin.attendance.student', $student))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attendance/StudentHistory')
                ->has('student')
                ->has('records')
            );
    }

    // -----------------------------------------------------------------------
    // Class summary
    // -----------------------------------------------------------------------

    public function test_class_summary_renders_counts(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();

        Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        AttendanceRecord::factory()->present()->create([
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
        ]);

        AttendanceRecord::factory()->absent()->create([
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
            'date'       => today()->subDay()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.summary', $classModel))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attendance/ClassSummary')
                ->has('classModel')
                ->has('summaries', 1)
                ->where('summaries.0.present', 1)
                ->where('summaries.0.absent', 1)
                ->where('summaries.0.total', 2)
            );
    }

    public function test_class_summary_date_range_filter(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();

        Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        // Three records spread across 30 days
        AttendanceRecord::factory()->present()->create([
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
            'date'       => today()->subDays(25)->toDateString(),
        ]);
        AttendanceRecord::factory()->absent()->create([
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
            'date'       => today()->subDays(10)->toDateString(),
        ]);
        AttendanceRecord::factory()->late()->create([
            'student_id' => $student->id,
            'class_id'   => $classModel->id,
            'date'       => today()->subDays(1)->toDateString(),
        ]);

        // Filter to last 15 days — should only see the absent + late records
        $this->actingAs($user)
            ->get(route('admin.attendance.summary', [
                'classModel' => $classModel->id,
                'date_from'  => today()->subDays(15)->toDateString(),
                'date_to'    => today()->toDateString(),
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('summaries.0.total', 2)
                ->where('summaries.0.present', 0)
                ->where('summaries.0.absent', 1)
                ->where('summaries.0.late', 1)
            );
    }

    public function test_class_summary_attendance_rate_calculation(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();

        Enrollment::factory()->create([
            'class_id'   => $classModel->id,
            'student_id' => $student->id,
            'status'     => 'enrolled',
        ]);

        // 3 present, 1 late, 1 absent, 1 excused = 6 total
        // rate = (present + late) / total = 4/6 = 66.7%
        foreach (['present', 'present', 'present', 'late', 'absent', 'excused'] as $i => $status) {
            AttendanceRecord::factory()->create([
                'student_id' => $student->id,
                'class_id'   => $classModel->id,
                'date'       => today()->subDays($i)->toDateString(),
                'status'     => $status,
            ]);
        }

        $this->actingAs($user)
            ->get(route('admin.attendance.summary', $classModel))
            ->assertInertia(fn (Assert $page) => $page
                ->where('summaries.0.total', 6)
                ->where('summaries.0.present', 3)
                ->where('summaries.0.late', 1)
                ->where('summaries.0.absent', 1)
                ->where('summaries.0.excused', 1)
                ->where('summaries.0.attendance_rate', 66.7)
            );
    }

    // -----------------------------------------------------------------------
    // Additional feature flag behaviour
    // -----------------------------------------------------------------------

    public function test_existing_records_preserved_when_disabled(): void
    {
        $this->enableAttendance();
        $user    = $this->siteAdmin();
        $student = Student::factory()->create();

        AttendanceRecord::factory()->count(5)->create(['student_id' => $student->id]);

        $this->assertSame(5, AttendanceRecord::count());

        // Disable the feature
        $this->actingAs($user)->post(route('admin.feature-settings.update'), [
            'attendance_enabled' => false,
        ]);

        // Records still exist
        $this->assertSame(5, AttendanceRecord::count());
    }

    public function test_toggle_requires_auth(): void
    {
        $this->post(route('admin.feature-settings.update'), [
            'attendance_enabled' => true,
        ])->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // Additional store validation
    // -----------------------------------------------------------------------

    public function test_store_requires_class_id(): void
    {
        $this->enableAttendance();
        $user    = $this->adminUser();
        $student = Student::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'date'    => today()->toDateString(),
            'records' => [['student_id' => $student->id, 'status' => 'present']],
        ])->assertSessionHasErrors(['class_id']);
    }

    public function test_store_requires_valid_date(): void
    {
        $this->enableAttendance();
        $user    = $this->adminUser();
        $class   = ClassModel::factory()->create();
        $student = Student::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_id' => $class->id,
            'date'     => 'not-a-date',
            'records'  => [['student_id' => $student->id, 'status' => 'present']],
        ])->assertSessionHasErrors(['date']);
    }

    public function test_store_requires_records_array(): void
    {
        $this->enableAttendance();
        $user  = $this->adminUser();
        $class = ClassModel::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_id' => $class->id,
            'date'     => today()->toDateString(),
        ])->assertSessionHasErrors(['records']);
    }

    public function test_store_saves_notes(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();
        $date       = today()->toDateString();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_id' => $classModel->id,
            'date'     => $date,
            'records'  => [
                ['student_id' => $student->id, 'status' => 'late', 'notes' => 'Arrived 10 minutes late'],
            ],
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'status'     => 'late',
            'notes'      => 'Arrived 10 minutes late',
        ]);
    }

    public function test_store_sets_marked_by(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $student    = Student::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_id' => $classModel->id,
            'date'     => today()->toDateString(),
            'records'  => [['student_id' => $student->id, 'status' => 'present']],
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'marked_by'  => $user->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // Take — date handling
    // -----------------------------------------------------------------------

    public function test_take_defaults_to_today(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.attendance.take', $classModel))
            ->assertInertia(fn (Assert $page) => $page
                ->where('date', today()->toDateString())
            );
    }

    public function test_take_uses_date_query_param(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();
        $date       = '2025-11-15';

        $this->actingAs($user)
            ->get(route('admin.attendance.take', ['classModel' => $classModel->id, 'date' => $date]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('date', $date)
            );
    }

    public function test_take_excludes_dropped_students(): void
    {
        $this->enableAttendance();
        $user       = $this->adminUser();
        $classModel = ClassModel::factory()->create();

        // Enrolled student
        Enrollment::factory()->create([
            'class_id' => $classModel->id,
            'status'   => 'enrolled',
        ]);

        // Dropped student — should not appear
        Enrollment::factory()->create([
            'class_id' => $classModel->id,
            'status'   => 'dropped',
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.take', $classModel))
            ->assertInertia(fn (Assert $page) => $page
                ->has('enrollments', 1)
            );
    }

    // -----------------------------------------------------------------------
    // Auth guards on remaining routes
    // -----------------------------------------------------------------------

    public function test_student_history_requires_auth(): void
    {
        $this->enableAttendance();
        $student = Student::factory()->create();

        $this->get(route('admin.attendance.student', $student))
            ->assertRedirect(route('login'));
    }

    public function test_class_summary_requires_auth(): void
    {
        $this->enableAttendance();
        $classModel = ClassModel::factory()->create();

        $this->get(route('admin.attendance.summary', $classModel))
            ->assertRedirect(route('login'));
    }

    public function test_index_search_filters_results(): void
    {
        $this->enableAttendance();
        $user = $this->adminUser();

        ClassModel::factory()->create(['section_name' => 'Alpha']);
        ClassModel::factory()->create(['section_name' => 'Beta']);

        $this->actingAs($user)
            ->get(route('admin.attendance.index', ['search' => 'Alpha']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'Alpha')
            );
    }
}
