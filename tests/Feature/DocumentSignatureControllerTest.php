<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentSignatureControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * [#test] creating a signature request successfully.
     *
     * @return void
     */
    public function test_create_signature_request_successfully()
    {
        $user = User::factory()->create();
        $requestedUser = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/document-signatures/create-requests', [
            'document_id' => $document->id,
            'requested_user_id' => $requestedUser->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('signature_requests', [
            'document_id' => $document->id,
            'requested_user_id' => $requestedUser->id,
            'status' => 'pending',
        ]);
    }

    /**
     * [#test] creating a signature request with an invalid document ID.
     *
     * @return void
     */
    public function test_create_signature_request_invalid_document_id()
    {
        $user = User::factory()->create();
        $requestedUser = User::factory()->create();
        $invalidDocumentId = (string) Str::uuid(); // This will generate a non-existent document ID

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/document-signatures/create-requests', [
            'document_id' => $invalidDocumentId,
            'requested_user_id' => $requestedUser->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('document_id');
    }

    /**
     * [#test] creating a signature request as unauthorized user.
     *
     * @return void
     */
    public function test_create_signature_request_unauthorized()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $requestedUser = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/document-signatures/create-requests', [
            'document_id' => $document->id,
            'requested_user_id' => $requestedUser->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * [#test] creating a signature request for an already signed document.
     *
     * @return void
     */
    public function test_create_signature_request_document_already_signed()
    {
        $user = User::factory()->create();
        $requestedUser = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id, 'status' => 'signed']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/document-signatures/create-requests', [
            'document_id' => $document->id,
            'requested_user_id' => $requestedUser->id,
        ]);

        $response->assertStatus(400);
    }
}
