<?php

namespace Tests\Feature\Admin;

use App\Models\Document;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function regularUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    private function enableDocuments(): void
    {
        Setting::set('feature_documents_enabled', '1', 'boolean');
    }

    // -----------------------------------------------------------------------
    // 1. All routes blocked when feature disabled
    // -----------------------------------------------------------------------

    public function test_all_routes_blocked_when_feature_disabled(): void
    {
        $admin = $this->adminUser();
        $doc   = Document::factory()->create();

        $this->actingAs($admin)->get(route('admin.documents.index'))->assertStatus(403);
        $this->actingAs($admin)->post(route('admin.documents.store'))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.documents.download', $doc))->assertStatus(403);
        $this->actingAs($admin)->delete(route('admin.documents.destroy', $doc))->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // 2. Feature flag shared as false by default
    // -----------------------------------------------------------------------

    public function test_feature_flag_shared_as_false_by_default(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) =>
                $page->where('features.documents_enabled', false)
            );
    }

    // -----------------------------------------------------------------------
    // 3. Feature flag shared as true when enabled
    // -----------------------------------------------------------------------

    public function test_feature_flag_shared_as_true_when_enabled(): void
    {
        $this->enableDocuments();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) =>
                $page->where('features.documents_enabled', true)
            );
    }

    // -----------------------------------------------------------------------
    // 4. Toggle enables documents (site_admin)
    // -----------------------------------------------------------------------

    public function test_toggle_enables_documents(): void
    {
        $siteAdmin = User::factory()->create(['role' => 'site_admin']);

        $this->actingAs($siteAdmin)
            ->post(route('admin.feature-settings.update'), ['documents_enabled' => true])
            ->assertRedirect();

        $this->assertSame('1', Setting::get('feature_documents_enabled'));
    }

    // -----------------------------------------------------------------------
    // 5. Toggle disables documents (site_admin)
    // -----------------------------------------------------------------------

    public function test_toggle_disables_documents(): void
    {
        $this->enableDocuments();
        $siteAdmin = User::factory()->create(['role' => 'site_admin']);

        $this->actingAs($siteAdmin)
            ->post(route('admin.feature-settings.update'), ['documents_enabled' => false])
            ->assertRedirect();

        $this->assertSame('0', Setting::get('feature_documents_enabled'));
    }

    // -----------------------------------------------------------------------
    // 6. Institution index renders
    // -----------------------------------------------------------------------

    public function test_institution_index_renders(): void
    {
        $this->enableDocuments();
        $admin = $this->adminUser();
        Document::factory()->count(3)->create();

        // Without filters, documents is null (deferred results)
        $this->actingAs($admin)
            ->get(route('admin.documents.index'))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Documents/Index')
                     ->where('documents', null)
                     ->where('searched', false)
            );

        // With a filter applied, results are returned
        $this->actingAs($admin)
            ->get(route('admin.documents.index', ['entity_type' => 'Institution']))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Documents/Index')
                     ->has('documents.data', 3)
                     ->where('searched', true)
            );
    }

    // -----------------------------------------------------------------------
    // 7. Index requires auth
    // -----------------------------------------------------------------------

    public function test_index_requires_auth(): void
    {
        $this->enableDocuments();

        $this->get(route('admin.documents.index'))->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // 8. Upload institution document
    // -----------------------------------------------------------------------

    public function test_upload_institution_document(): void
    {
        $this->enableDocuments();
        Storage::fake('local');
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'entity_type' => 'Institution',
                'entity_id'   => 0,
                'file'        => UploadedFile::fake()->create('policy.pdf', 100, 'application/pdf'),
                'category'    => 'Policy',
                'description' => 'Test policy doc',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'entity_type' => 'Institution',
            'entity_id'   => 0,
            'category'    => 'Policy',
        ]);
    }

    // -----------------------------------------------------------------------
    // 9. Upload student document
    // -----------------------------------------------------------------------

    public function test_upload_student_document(): void
    {
        $this->enableDocuments();
        Storage::fake('local');
        $admin   = $this->adminUser();
        $student = Student::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'entity_type' => 'Student',
                'entity_id'   => $student->id,
                'file'        => UploadedFile::fake()->create('iep.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'entity_type' => 'Student',
            'entity_id'   => $student->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // 10. Upload employee document
    // -----------------------------------------------------------------------

    public function test_upload_employee_document(): void
    {
        $this->enableDocuments();
        Storage::fake('local');
        $admin    = $this->adminUser();
        $employee = Employee::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'entity_type' => 'Employee',
                'entity_id'   => $employee->id,
                'file'        => UploadedFile::fake()->create('contract.pdf', 150, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'entity_type' => 'Employee',
            'entity_id'   => $employee->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // 11. Upload rejects oversized file (>10 MB)
    // -----------------------------------------------------------------------

    public function test_upload_rejects_oversized_file(): void
    {
        $this->enableDocuments();
        Storage::fake('local');
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'entity_type' => 'Institution',
                'entity_id'   => 0,
                'file'        => UploadedFile::fake()->create('big.pdf', 11_000, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');
    }

    // -----------------------------------------------------------------------
    // 12. Upload rejects invalid entity_type
    // -----------------------------------------------------------------------

    public function test_upload_rejects_invalid_entity_type(): void
    {
        $this->enableDocuments();
        Storage::fake('local');
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'entity_type' => 'Guardian',
                'entity_id'   => 1,
                'file'        => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('entity_type');
    }

    // -----------------------------------------------------------------------
    // 13. Upload requires file
    // -----------------------------------------------------------------------

    public function test_upload_requires_file(): void
    {
        $this->enableDocuments();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'entity_type' => 'Institution',
                'entity_id'   => 0,
            ])
            ->assertSessionHasErrors('file');
    }

    // -----------------------------------------------------------------------
    // 14. Download streams file
    // -----------------------------------------------------------------------

    public function test_download_streams_file(): void
    {
        $this->enableDocuments();
        Storage::fake('local');
        $admin = $this->adminUser();

        // Put a fake file on disk
        Storage::disk('local')->put('documents/Institution/0/test.pdf', 'fake content');

        $doc = Document::factory()->create([
            'stored_path'   => 'documents/Institution/0/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type'     => 'application/pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.documents.download', $doc))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    // -----------------------------------------------------------------------
    // 15. Download 404 when file missing on disk
    // -----------------------------------------------------------------------

    public function test_download_404_when_file_missing_on_disk(): void
    {
        $this->enableDocuments();
        Storage::fake('local');
        $admin = $this->adminUser();

        $doc = Document::factory()->create([
            'stored_path' => 'documents/Institution/0/nonexistent.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.documents.download', $doc))
            ->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // 16. Destroy deletes document and file
    // -----------------------------------------------------------------------

    public function test_destroy_deletes_document_and_file(): void
    {
        $this->enableDocuments();
        Storage::fake('local');
        $admin = $this->adminUser();

        Storage::disk('local')->put('documents/Institution/0/test.pdf', 'content');

        $doc = Document::factory()->create([
            'stored_path' => 'documents/Institution/0/test.pdf',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.documents.destroy', $doc))
            ->assertRedirect();

        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
        Storage::disk('local')->assertMissing('documents/Institution/0/test.pdf');
    }

    // -----------------------------------------------------------------------
    // 17. Destroy requires admin
    // -----------------------------------------------------------------------

    public function test_destroy_requires_admin(): void
    {
        $this->enableDocuments();
        $user = $this->regularUser();
        $doc  = Document::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.documents.destroy', $doc))
            ->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // 18. Student show includes documents when enabled
    // -----------------------------------------------------------------------

    public function test_student_show_includes_documents_when_enabled(): void
    {
        $this->enableDocuments();
        $admin   = $this->adminUser();
        $student = Student::factory()->create();

        Document::factory()->forStudent($student->id)->count(2)->create();

        $this->actingAs($admin)
            ->get(route('admin.students.show', $student))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Students/Show')
                     ->where('documentsEnabled', true)
                     ->has('documents', 2)
            );
    }

    // -----------------------------------------------------------------------
    // 19. Student show documents empty when disabled
    // -----------------------------------------------------------------------

    public function test_student_show_documents_empty_when_disabled(): void
    {
        $admin   = $this->adminUser();
        $student = Student::factory()->create();

        Document::factory()->forStudent($student->id)->create();

        $this->actingAs($admin)
            ->get(route('admin.students.show', $student))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Students/Show')
                     ->where('documentsEnabled', false)
                     ->has('documents', 0)
            );
    }

    // -----------------------------------------------------------------------
    // 20. Employee show includes documents when enabled
    // -----------------------------------------------------------------------

    public function test_employee_show_includes_documents_when_enabled(): void
    {
        $this->enableDocuments();
        $admin    = $this->adminUser();
        $employee = Employee::factory()->create();

        Document::factory()->forEmployee($employee->id)->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.employees.show', $employee))
            ->assertInertia(fn (Assert $page) =>
                $page->component('Admin/Employees/Show')
                     ->where('documentsEnabled', true)
                     ->has('documents', 3)
            );
    }
}
