<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Document;
use App\Models\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentSignatureControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test] public function an_authenticated_user_can_sign_a_document()
    {
        // Create a user, document, and signature
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id]);
        $signature = Signature::factory()->create(['user_id' => $user->id]);

        // Authenticate the user
        Sanctum::actingAs($user);

        // API request
        $response = $this->postJson("/api/documents/{$document->id}/sign", [
            'signature_id' => $signature->id,
        ]);

//        dd($response->exception);

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
    }

    #[Test] public function a_guest_user_cannot_sign_a_document()
    {
        // Create a user, document, and signature
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id]);
        $signature = Signature::factory()->create(['user_id' => $user->id]);

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
    }

    #[Test] public function it_requires_a_valid_signature_id()
    {
        // Create a user and document
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id]);

        // Authenticate the user
        Sanctum::actingAs($user);

        // API request with invalid data
        $response = $this->postJson("/api/documents/{$document->id}/sign", [
            'signature_id' => 999, // No user for id 999
        ]);

        // Check the response status and errors
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['signature_id']);
    }
}
