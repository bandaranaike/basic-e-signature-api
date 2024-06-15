<?php

namespace Tests\Feature;

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
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Creating a fake doc
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        // API request
        $response = $this->postJson('/api/documents/upload', [
            'name' => 'Test Document',
            'file' => $file,
        ]);

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
            'name' => 'Test Document',
            'file' => 'documents/' . $file->hashName(),
            'user_id' => $user->id,
        ]);
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
}
