<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Document;
use App\Models\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SignatureControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // Mocking the RSA keys
        Storage::put('keys/private_key.pem', file_get_contents(base_path('tests/fixtures/private_key.pem')));
        Storage::put('keys/public_key.pem', file_get_contents(base_path('tests/fixtures/public_key.pem')));
    }

    #[Test] public function sign_document()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        // Create a fake PDF file in the storage
        Storage::disk('local')->put($document->file_path, 'Sample Document Content');

        $response = $this->actingAs($user, 'sanctum')->postJson(route('sign.document', ['documentId' => $document->id]), []);

        $response->assertStatus(201);
        $response->assertJsonStructure(['signature' => ['id', 'document_id', 'user_id', 'signature_file_path', 'signature_hash']]);

        $signature = Signature::where('document_id', $document->id)->first();
        Storage::disk('local')->assertExists($signature->signature_file_path);
    }

    #[Test] public function sign_document_with_non_pending_status()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id, 'status' => 'signed']);

        // Create a fake PDF file in the storage
        Storage::disk('local')->put($document->file_path, 'Sample Document Content');

        $response = $this->actingAs($user, 'sanctum')->postJson(route('sign.document', ['documentId' => $document->id]), []);

//        dd($response->json());

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Document is not pending for signature']);

    }

    #[Test] public function verify_signature_valid()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id, 'status' => 'signed']);

        $documentContent = 'Sample Document Content';
        Storage::disk('local')->put($document->file_path, $documentContent);

        $documentHash = hash('sha256', $documentContent);
        $privateKey = openssl_pkey_get_private(Storage::get('keys/private_key.pem'));

        $signature = '';
        openssl_sign($documentHash, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $signaturePath = 'signatures/' . Str::uuid() . '.sig';
        Storage::disk('local')->put($signaturePath, $signature);

        Signature::factory()->create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'signature_file_path' => $signaturePath,
            'signature_hash' => base64_encode($signature),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson(route('verify.signature', ['documentId' => $document->id]));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Signature is valid']);
    }

    #[Test] public function verify_signature_invalid()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id, 'status' => 'signed']);

        // Original document content
        $originalContent = 'Original Document Content';
        Storage::disk('local')->put($document->file_path, $originalContent);

        // Different content for generating an invalid signature
        $differentContent = 'Different Document Content';
        $differentDocumentHash = hash('sha256', $differentContent);
        $privateKey = openssl_pkey_get_private(Storage::get('keys/private_key.pem'));

        $invalidSignature = '';
        openssl_sign($differentDocumentHash, $invalidSignature, $privateKey, OPENSSL_ALGO_SHA256);

        $signaturePath = 'signatures/' . Str::uuid() . '.sig';
        Storage::disk('local')->put($signaturePath, $invalidSignature);

        Signature::factory()->create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'signature_file_path' => $signaturePath,
            'signature_hash' => base64_encode($invalidSignature),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson(route('verify.signature', ['documentId' => $document->id]));

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Signature is invalid']);
    }
}
