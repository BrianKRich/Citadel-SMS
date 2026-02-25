<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\ClassModel;
use App\Models\ClassCourse;
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

    private function makeClassCourse(array $overrides = []): ClassCourse
    {
        $class = ClassModel::factory()->create();

        return ClassCourse::factory()->create(array_merge(
            ['class_id' => $class->id],
            $overrides
        ));
    }

    // -----------------------------------------------------------------------
    // Feature flag tests
    // -----------------------------------------------------------------------

    public function test_routes_blocked_when_attendance_disabled(): void
    {
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        $this->actingAs($user)->get(route('admin.attendance.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.attendance.take', $classCourse))->assertStatus(403);
        $this->actingAs($user)->post(route('admin.attendance.store'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.attendance.student', $student))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.attendance.summary', $classCourse))->assertStatus(403);
    }

    public function test_routes_accessible_when_attendance_enabled(): void
    {
        $this->enableAttendance();
        $user = $this->adminUser();

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

    public function test_index_renders_class_course_list(): void
    {
        $this->enableAttendance();
        $user = $this->adminUser();
        $this->makeClassCourse();

        $this->actingAs($user)
            ->get(route('admin.attendance.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attendance/Index')
                ->has('classCourses')
                ->has('filters')
            );
    }

    // -----------------------------------------------------------------------
    // Take
    // -----------------------------------------------------------------------

    public function test_take_renders_enrolled_students(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        Enrollment::factory()->create([
            'class_course_id' => $classCourse->id,
            'student_id'       => $student->id,
            'status'           => 'enrolled',
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.take', $classCourse))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attendance/Take')
                ->has('classCourse')
                ->has('enrollments', 1)
                ->has('existingRecords')
                ->has('date')
            );
    }

    public function test_take_prefills_existing_records(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        Enrollment::factory()->create([
            'class_course_id' => $classCourse->id,
            'student_id'       => $student->id,
            'status'           => 'enrolled',
        ]);

        $date = today()->toDateString();

        AttendanceRecord::factory()->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
            'date'             => $date,
            'status'           => 'absent',
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.take', ['classCourse' => $classCourse->id, 'date' => $date]))
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
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        Enrollment::factory()->create([
            'class_course_id' => $classCourse->id,
            'student_id'       => $student->id,
            'status'           => 'enrolled',
        ]);

        $date = today()->toDateString();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_course_id' => $classCourse->id,
            'date'             => $date,
            'records'          => [
                ['student_id' => $student->id, 'status' => 'present', 'notes' => null],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance_records', [
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
            'date'             => $date,
            'status'           => 'present',
        ]);
    }

    public function test_store_upserts_same_date(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();
        $date         = today()->toDateString();

        AttendanceRecord::factory()->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
            'date'             => $date,
            'status'           => 'absent',
        ]);

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_course_id' => $classCourse->id,
            'date'             => $date,
            'records'          => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ]);

        $this->assertSame(
            1,
            AttendanceRecord::where('student_id', $student->id)
                ->where('class_course_id', $classCourse->id)
                ->whereDate('date', $date)
                ->count()
        );

        $this->assertDatabaseHas('attendance_records', [
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
            'status'           => 'present',
        ]);
    }

    public function test_store_rejects_invalid_status(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_course_id' => $classCourse->id,
            'date'             => today()->toDateString(),
            'records'          => [
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

        $classCourse = $this->makeClassCourse();
        AttendanceRecord::factory()->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
        ]);

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
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        Enrollment::factory()->create([
            'class_course_id' => $classCourse->id,
            'student_id'       => $student->id,
            'status'           => 'enrolled',
        ]);

        AttendanceRecord::factory()->present()->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
        ]);

        AttendanceRecord::factory()->absent()->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
            'date'             => today()->subDay()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.summary', $classCourse))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attendance/ClassSummary')
                ->has('classCourse')
                ->has('summaries', 1)
                ->where('summaries.0.present', 1)
                ->where('summaries.0.absent', 1)
                ->where('summaries.0.total', 2)
            );
    }

    public function test_class_summary_date_range_filter(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        Enrollment::factory()->create([
            'class_course_id' => $classCourse->id,
            'student_id'       => $student->id,
            'status'           => 'enrolled',
        ]);

        // Three records spread across 30 days
        AttendanceRecord::factory()->present()->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
            'date'             => today()->subDays(25)->toDateString(),
        ]);
        AttendanceRecord::factory()->absent()->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
            'date'             => today()->subDays(10)->toDateString(),
        ]);
        AttendanceRecord::factory()->late()->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
            'date'             => today()->subDays(1)->toDateString(),
        ]);

        // Filter to last 15 days — should only see the absent + late records
        $this->actingAs($user)
            ->get(route('admin.attendance.summary', [
                'classCourse' => $classCourse->id,
                'date_from'    => today()->subDays(15)->toDateString(),
                'date_to'      => today()->toDateString(),
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
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        Enrollment::factory()->create([
            'class_course_id' => $classCourse->id,
            'student_id'       => $student->id,
            'status'           => 'enrolled',
        ]);

        // 3 present, 1 late, 1 absent, 1 excused = 6 total
        // rate = (present + late) / total = 4/6 = 66.7%
        foreach (['present', 'present', 'present', 'late', 'absent', 'excused'] as $i => $status) {
            AttendanceRecord::factory()->create([
                'student_id'       => $student->id,
                'class_course_id' => $classCourse->id,
                'date'             => today()->subDays($i)->toDateString(),
                'status'           => $status,
            ]);
        }

        $this->actingAs($user)
            ->get(route('admin.attendance.summary', $classCourse))
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
        $user         = $this->siteAdmin();
        $student      = Student::factory()->create();
        $classCourse = $this->makeClassCourse();

        AttendanceRecord::factory()->count(5)->create([
            'student_id'       => $student->id,
            'class_course_id' => $classCourse->id,
        ]);

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

    public function test_store_requires_class_course_id(): void
    {
        $this->enableAttendance();
        $user    = $this->adminUser();
        $student = Student::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'date'    => today()->toDateString(),
            'records' => [['student_id' => $student->id, 'status' => 'present']],
        ])->assertSessionHasErrors(['class_course_id']);
    }

    public function test_store_requires_valid_date(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_course_id' => $classCourse->id,
            'date'             => 'not-a-date',
            'records'          => [['student_id' => $student->id, 'status' => 'present']],
        ])->assertSessionHasErrors(['date']);
    }

    public function test_store_requires_records_array(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_course_id' => $classCourse->id,
            'date'             => today()->toDateString(),
        ])->assertSessionHasErrors(['records']);
    }

    public function test_store_saves_notes(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();
        $date         = today()->toDateString();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_course_id' => $classCourse->id,
            'date'             => $date,
            'records'          => [
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
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $student      = Student::factory()->create();

        $this->actingAs($user)->post(route('admin.attendance.store'), [
            'class_course_id' => $classCourse->id,
            'date'             => today()->toDateString(),
            'records'          => [['student_id' => $student->id, 'status' => 'present']],
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
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();

        $this->actingAs($user)
            ->get(route('admin.attendance.take', $classCourse))
            ->assertInertia(fn (Assert $page) => $page
                ->where('date', today()->toDateString())
            );
    }

    public function test_take_uses_date_query_param(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();
        $date         = '2025-11-15';

        $this->actingAs($user)
            ->get(route('admin.attendance.take', ['classCourse' => $classCourse->id, 'date' => $date]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('date', $date)
            );
    }

    public function test_take_excludes_dropped_students(): void
    {
        $this->enableAttendance();
        $user         = $this->adminUser();
        $classCourse = $this->makeClassCourse();

        // Enrolled student
        Enrollment::factory()->create([
            'class_course_id' => $classCourse->id,
            'status'           => 'enrolled',
        ]);

        // Dropped student — should not appear
        Enrollment::factory()->create([
            'class_course_id' => $classCourse->id,
            'status'           => 'dropped',
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.take', $classCourse))
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
        $classCourse = $this->makeClassCourse();

        $this->get(route('admin.attendance.summary', $classCourse))
            ->assertRedirect(route('login'));
    }

    public function test_index_search_filters_results(): void
    {
        $this->enableAttendance();
        $user = $this->adminUser();

        $this->makeClassCourse();
        $this->makeClassCourse();

        $this->actingAs($user)
            ->get(route('admin.attendance.index', ['search' => 'Room']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'Room')
            );
    }
}
