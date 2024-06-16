<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Document;
use App\Models\SignatureRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test] public function uploading_a_document_successfully()
    {
        Storage::fake('documents');

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/documents', [
            'title' => 'Test Document',
            'file' => UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf')
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'document' => [
                'id',
                'user_id',
                'title',
                'file_path',
                'status',
                'created_at',
                'updated_at'
            ]
        ]);

        // Extract the file path from the response
        $filePath = $response->json('document.file_path');

        // Assert the file exists in the fake storage
        Storage::disk('documents')->assertExists($filePath);
    }

    #[Test] public function getting_user_documents_with_pagination()
    {
        $user = User::factory()->create();
        Document::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/documents?per_page=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ]);
        $this->assertCount(5, $response->json('data'));
    }

    #[Test] public function getting_sign_requested_documents_with_pagination()
    {
        $user = User::factory()->create();
        $requestedUser = User::factory()->create();

        $documents = Document::factory()->count(10)->create(['user_id' => $user->id]);
        foreach ($documents as $document) {
            SignatureRequest::factory()->create([
                'document_id' => $document->id,
                'requester_id' => $user->id,
                'requested_user_id' => $requestedUser->id
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/documents/sign-requested?per_page=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ]);
        $this->assertCount(5, $response->json('data'));
    }
}
