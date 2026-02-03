<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_manager_can_upload_document()
    {
        Storage::fake('documents');

        // Find a manager
        $manager = User::role('manager')->first();
        $category = DocumentCategory::first();

        // Create a real temp file in storage provided it is writable
        $path = storage_path('app/temp_test_doc.pdf');
        file_put_contents($path, '%PDF-1.4' . PHP_EOL . 'dummy content');

        $file = new UploadedFile($path, 'document.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($manager)->postJson('/api/documents', [
            'title' => 'Test Document',
            'file' => $file,
            'document_category_id' => $category->id,
            'department_id' => $manager->department_id, // Own department
            'access_level' => 'department',
        ]);

        $response->assertStatus(201);

        @unlink($path);
    }

    public function test_employee_cannot_upload_document()
    {
        Storage::fake('documents');

        $employee = User::role('employee')->first();
        $category = DocumentCategory::first();

        $path = storage_path('app/temp_test_doc_fail.pdf');
        file_put_contents($path, 'dummy content');
        $file = new UploadedFile($path, 'document.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($employee)->postJson('/api/documents', [
            'title' => 'Test Document',
            'file' => $file,
            'document_category_id' => $category->id,
            'department_id' => $employee->department_id,
            'access_level' => 'department',
        ]);

        $response->assertStatus(403);

        @unlink($path);
    }

    public function test_user_can_search_documents()
    {
        $user = User::first();

        // Create a doc
        $doc = Document::create([
            'title' => 'UniqueSearchTerm',
            'file_name' => 'test.pdf',
            'file_path' => 'documents/test.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'document_category_id' => 1,
            'department_id' => 1,
            'uploaded_by' => 1,
            'access_level' => 'public',
        ]);

        $response = $this->actingAs($user)->getJson('/api/documents?search=UniqueSearchTerm');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'UniqueSearchTerm']);
    }

    public function test_user_can_filter_documents()
    {
        $user = User::first();
        $category = DocumentCategory::first();

        // Create a doc with this category
        $doc = Document::create([
            'title' => 'FilteredDoc',
            'file_name' => 'test.pdf',
            'file_path' => 'documents/test.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'document_category_id' => $category->id,
            'department_id' => 1,
            'uploaded_by' => 1,
            'access_level' => 'public',
        ]);

        $response = $this->actingAs($user)->getJson('/api/documents?category_id=' . $category->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'FilteredDoc']);
    }

    public function test_admin_can_delete_document()
    {
        Storage::fake('documents');

        $admin = User::role('admin')->first();

        $doc = Document::create([
            'title' => 'DeleteMe',
            'file_name' => 'test.pdf',
            'file_path' => 'documents/test.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'document_category_id' => 1,
            'department_id' => 1,
            'uploaded_by' => 1, // Doesn't matter for admin
            'access_level' => 'public',
        ]);

        $response = $this->actingAs($admin)->deleteJson('/api/documents/' . $doc->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
    }
}
