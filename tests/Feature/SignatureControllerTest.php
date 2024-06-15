<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SignatureControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Storage directory should cleaned up before and after each test
        Storage::fake('public');
    }

    #[Test] public function an_authenticated_user_can_upload_a_signature()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a fake file
        $file = UploadedFile::fake()->image('signature.png');

        // API request
        $response = $this->postJson('/api/signatures/upload', [
            'file' => $file,
        ]);

        // Check the response status and structure
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'signature' => [
                    'id',
                    'public_id',
                    'file',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Check the file has stored
        Storage::disk('public')->assertExists('signatures/' . $file->hashName());

        // Check the signature record
        $this->assertDatabaseHas('signatures', [
            'file' => 'signatures/' . $file->hashName(),
            'user_id' => $user->id,
        ]);
    }

    #[Test] public function a_guest_user_cannot_upload_a_signature()
    {
        // Create a fake file
        $file = UploadedFile::fake()->image('signature.png');

        // API request without authentication
        $response = $this->postJson('/api/signatures/upload', [
            'file' => $file,
        ]);

        // Check the response status
        $response->assertStatus(401);

        // Check the file is not there
        Storage::disk('public')->assertMissing('signatures/' . $file->hashName());
    }

    #[Test] public function it_requires_a_valid_file()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // API request with invalid data
        $response = $this->postJson('/api/signatures/upload', [
            'file' => 'not_a_file',
        ]);

        // Check the response status and errors
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
}
