<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Ensure storage directory is cleaned up before and after each test
        Storage::fake('public');
    }

    #[Test] public function an_authenticated_user_can_upload_a_document()
    {
        // API request
        list($response, $file, $user) = $this->upload_fake_document(1);

        // Check the response status and structure
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'document' => [
                    'id',
                    'public_id',
                    'name',
                    'file',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Make sure the file was stored
        Storage::disk('public')->assertExists('documents/' . $file->hashName());

        // Database should contain the document
        $this->assertDatabaseHas('documents', [
            'name' => 'Test Document 1',
            'file' => 'documents/' . $file->hashName(),
            'user_id' => $user->id,
        ]);
    }

    private function upload_fake_document($documentId): array
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create("document-$documentId.pdf", 1024, 'application/pdf');

        $response = $this->postJson('/api/documents/upload', [
            'name' => "Test Document $documentId",
            'file' => $file,
        ]);
        return [$response, $file, $user];
    }

    #[Test] public function a_guest_user_cannot_upload_a_document()
    {

        // A fake doc creating
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        // API request without authentication
        $response = $this->postJson('/api/documents/upload', [
            'name' => 'Test Document',
            'file' => $file,
        ]);

        // check the response status
        $response->assertStatus(401);

        // File should not be there
        Storage::disk('public')->assertMissing('documents/' . $file->hashName());
    }

    #[Test] public function it_requires_a_valid_file_and_name()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // API request with invalid data
        $response = $this->postJson('/api/documents/upload', [
            'name' => '',
            'file' => 'not_a_file',
        ]);

        // Expect response status and errors
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'file']);
    }

    #[Test] public function a_user_has_documents()
    {
        $count = 3;

        for ($i = 0; $i < $count; $i++) {
            $this->upload_fake_document($i);
        }


        $response = $this->getJson('/api/documents');

        dd($response->json());

        $response->assertStatus(200)->assertJsonCount($count);
    }

    public function test_user_document_list()
    {
        $user = User::factory()->create();
        $document1 = Document::factory()->create(['user_id' => $user->id]);
        $document2 = Document::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user/documents');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'documents' => [
                '*' => ['id', 'public_id', 'name', 'file', 'user_id', 'created_at', 'updated_at']
            ]
        ]);

        $documents = $response->json('documents');
        $this->assertCount(2, $documents);
        $this->assertEquals($document1->id, $documents[0]['id']);
        $this->assertEquals($document2->id, $documents[1]['id']);
    }

    public function test_user_document_list_empty()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user/documents');

        $response->assertStatus(200);
        $response->assertJson(['documents' => []]);
    }
}
