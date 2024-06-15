<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Document;
use App\Models\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentSignatureControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function an_authenticated_user_can_sign_a_document()
    {
        // Create a user, document, and signature
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id, 'file' => 'documents/sample.pdf']);
        $signature = Signature::factory()->create(['user_id' => $user->id, 'file' => 'signatures/sample-signature.png']);

        // Create a fake PDF file
        Storage::disk('public')->put('documents/sample.pdf', 'Fake PDF content');

        // Create a fake signature image
        Storage::disk('public')->put('signatures/sample-signature.png', 'Fake Signature Image');

        // Authenticate the user
        Sanctum::actingAs($user);

        // API request
        $response = $this->postJson("/api/documents/{$document->id}/sign", [
            'signature_id' => $signature->id,
        ]);

        // Check the response status
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Document signed successfully',
            ]);

        // Check the database has the document signature
        $this->assertDatabaseHas('document_signature', [
            'document_id' => $document->id,
            'signature_id' => $signature->id,
            'signed_user_id' => $user->id,
        ]);

        // Check that the new signed PDF exists in storage
        $newPdfPath = 'documents/signed_' . basename($document->file);
        Storage::disk('public')->assertExists($newPdfPath);
    }

    /** @test */
    public function a_guest_user_cannot_sign_a_document()
    {
        // Create a user, document, and signature
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id, 'file' => 'documents/sample.pdf']);
        $signature = Signature::factory()->create(['user_id' => $user->id, 'file' => 'signatures/sample-signature.png']);

        // Create a fake PDF file
        Storage::disk('public')->put('documents/sample.pdf', 'Fake PDF content');

        // Create a fake signature image
        Storage::disk('public')->put('signatures/sample-signature.png', 'Fake Signature Image');

        // API request without authentication
        $response = $this->postJson("/api/documents/{$document->id}/sign", [
            'signature_id' => $signature->id,
        ]);

        // Check the response status
        $response->assertStatus(401);

        // The database should not have the document signature
        $this->assertDatabaseMissing('document_signature', [
            'document_id' => $document->id,
            'signature_id' => $signature->id,
        ]);

        // Check that the new signed PDF does not exist in storage
        $newPdfPath = 'documents/signed_' . basename($document->file);
        Storage::disk('public')->assertMissing($newPdfPath);
    }

    /** @test */
    public function it_requires_a_valid_signature_id()
    {
        // Create a user and document
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id, 'file' => 'documents/sample.pdf']);

        // Authenticate the user
        Sanctum::actingAs($user);

        // API request with invalid data
        $response = $this->postJson("/api/documents/{$document->id}/sign", [
            'signature_id' => 999, // Non-existent signature ID
        ]);

        // Check the response status and errors
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['signature_id']);
    }
}
