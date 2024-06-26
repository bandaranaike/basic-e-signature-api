<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureController extends Controller
{
    /**
     * One of the most important functionalities of the app.
     * Allows a user to sign a document.
     * @param Request $request
     * @param $documentId
     * @return JsonResponse
     */
    public function signDocument(Request $request, $documentId): JsonResponse
    {
        try {
            $document = Document::findOrFail($documentId);

            if ($document->status !== 'pending') {
                return new JsonResponse(['error' => 'Document is not pending for signature'], 400);
            }

            $privateKey = $this->loadPrivateKey();

            if (!$privateKey) {
                return new JsonResponse(['error' => 'Unable to load private key'], 500);
            }

            $documentHash = $this->createDocumentHash($document->file_path);

            $signature = $this->generateSignature($documentHash, $privateKey);

            if (!$signature) {
                return new JsonResponse(['error' => 'Unable to generate signature'], 500);
            }

            $signatureFilePath = $this->storeSignature($signature);

            $signatureRecord = $this->createSignatureRecord($documentId, $request->user()->id, $signatureFilePath, $signature);

            $document->status = 'signed';
            $document->save();

            return new JsonResponse(['signature' => $signatureRecord], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while signing the document', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verifies whether a document has been altered after it was signed.
     * This is a crucial function in the application.
     *
     * @param $documentId
     * @return JsonResponse
     */
    public function verifySignature($documentId): JsonResponse
    {
        try {
            $signature = Signature::where('document_id', $documentId)->firstOrFail();
            $document = Document::findOrFail($documentId);

            $publicKey = $this->loadPublicKey();

            if (!$publicKey) {
                return new JsonResponse(['error' => 'Unable to load public key'], 500);
            }

            $documentHash = $this->createDocumentHash($document->file_path);

            $signatureContent = base64_decode($signature->signature_hash);
            $result = openssl_verify($documentHash, $signatureContent, $publicKey, OPENSSL_ALGO_SHA256);

            if ($result === 1) {
                return new JsonResponse(['message' => 'Signature is valid']);
            } elseif ($result === 0) {
                return new JsonResponse(['message' => 'Signature is invalid'], 400);
            } else {
                return new JsonResponse(['error' => 'An error occurred during verification'], 500);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while verifying the signature', 'message' => $e->getMessage()], 500);
        }
    }

    private function loadPrivateKey(): \OpenSSLAsymmetricKey|false
    {
        return openssl_pkey_get_private(Storage::get('keys/private_key.pem'));
    }

    private function loadPublicKey(): \OpenSSLAsymmetricKey|false
    {
        return openssl_pkey_get_public(Storage::get('keys/public_key.pem'));
    }

    private function createDocumentHash($filePath): string
    {
        $documentPath = Storage::path($filePath);
        $documentContent = file_get_contents($documentPath);
        return hash('sha256', $documentContent);
    }

    private function generateSignature($documentHash, $privateKey): string
    {
        $signature = '';
        openssl_sign($documentHash, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return $signature;
    }

    private function storeSignature($signature): string
    {
        try {
            $signatureFilePath = 'signatures/' . Str::uuid() . '.sig';
            Storage::put($signatureFilePath, $signature);
            return $signatureFilePath;
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to store signature');
        }
    }

    private function createSignatureRecord($documentId, $userId, $signatureFilePath, $signature)
    {
        try {
            return Signature::create([
                'id' => Str::uuid(),
                'document_id' => $documentId,
                'user_id' => $userId,
                'signature_file_path' => $signatureFilePath,
                'signature_hash' => base64_encode($signature),
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to create signature record');
        }
    }
}
