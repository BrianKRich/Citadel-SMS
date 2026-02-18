<?php

namespace Tests\Feature\Admin;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name'      => 'Jane',
            'last_name'       => 'Doe',
            'date_of_birth'   => '2005-06-15',
            'gender'          => 'female',
            'enrollment_date' => '2024-08-01',
            'status'          => 'active',
        ], $overrides);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('admin.students.index'))->assertRedirect(route('login'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders_student_list(): void
    {
        Student::factory()->count(3)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.students.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Students/Index')
                ->has('students')
                ->has('filters')
            );
    }

    public function test_index_filters_by_search(): void
    {
        Student::factory()->create(['first_name' => 'Unique', 'last_name' => 'Snowflake']);
        Student::factory()->count(2)->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.students.index', ['search' => 'Snowflake']));

        $response->assertInertia(fn (Assert $p) => $p
            ->component('Admin/Students/Index')
            ->where('students.total', 1)
        );
    }

    public function test_index_filters_by_status(): void
    {
        Student::factory()->create(['status' => 'graduated']);
        Student::factory()->count(2)->create(['status' => 'active']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.students.index', ['status' => 'graduated']));

        $response->assertInertia(fn (Assert $p) => $p
            ->where('students.total', 1)
        );
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_renders_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.students.create'))
            ->assertInertia(fn (Assert $p) => $p->component('Admin/Students/Create'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_student_and_redirects(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.students.store'), $this->validPayload());

        $student = Student::first();
        $response->assertRedirect(route('admin.students.show', $student));
        $this->assertDatabaseHas('students', ['first_name' => 'Jane', 'last_name' => 'Doe']);
        $this->assertStringStartsWith('STU-', $student->student_id);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.students.store'), []);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'date_of_birth', 'gender', 'enrollment_date', 'status']);
    }

    public function test_store_validates_gender_enum(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.students.store'), $this->validPayload(['gender' => 'alien']));

        $response->assertSessionHasErrors(['gender']);
    }

    public function test_store_validates_status_enum(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.students.store'), $this->validPayload(['status' => 'expelled']));

        $response->assertSessionHasErrors(['status']);
    }

    public function test_store_validates_unique_email(): void
    {
        Student::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.students.store'), $this->validPayload(['email' => 'taken@example.com']));

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_uploads_photo(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->post(route('admin.students.store'), $this->validPayload([
                'photo' => UploadedFile::fake()->image('photo.jpg'),
            ]));

        $student = Student::first();
        $this->assertNotNull($student->photo);
        Storage::disk('public')->assertExists($student->photo);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_renders_student_profile(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.students.show', $student))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Students/Show')
                ->has('student')
            );
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_edit_renders_form_with_student_data(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.students.edit', $student))
            ->assertInertia(fn (Assert $p) => $p
                ->component('Admin/Students/Edit')
                ->has('student')
            );
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin())
            ->patch(route('admin.students.update', $student), $this->validPayload([
                'first_name' => 'Updated',
                'last_name'  => 'Name',
            ]));

        $response->assertRedirect(route('admin.students.show', $student));
        $this->assertDatabaseHas('students', ['id' => $student->id, 'first_name' => 'Updated']);
    }

    public function test_update_allows_same_email_for_same_student(): void
    {
        $student = Student::factory()->create(['email' => 'me@example.com']);

        $response = $this->actingAs($this->admin())
            ->patch(route('admin.students.update', $student), $this->validPayload([
                'email' => 'me@example.com',
            ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_soft_deletes_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.students.destroy', $student));

        $response->assertRedirect(route('admin.students.index'));
        $this->assertSoftDeleted('students', ['id' => $student->id]);
    }

    public function test_destroyed_student_not_visible_in_index(): void
    {
        $student = Student::factory()->create(['first_name' => 'GhostUser']);
        $student->delete();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.students.index', ['search' => 'GhostUser']));

        $response->assertInertia(fn (Assert $p) => $p
            ->where('students.total', 0)
        );
    }
}
