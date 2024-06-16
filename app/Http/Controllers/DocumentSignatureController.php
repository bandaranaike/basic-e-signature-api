<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignDocumentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\SignatureRequest;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentSignatureController extends Controller
{

    public function createSignatureRequest(SignDocumentRequest $request): JsonResponse
    {

        $document = Document::findOrFail($request->document_id);

        $this->authorizeUser($document);
        $this->checkDocumentStatus($document);

        $signatureRequest = $this->createNewSignatureRequest($request, $document);

        return new JsonResponse(['message' => 'Signature request created successfully', 'signature_request' => $signatureRequest], 201);
    }

    /**
     * Check if the authenticated user is the owner of the document.
     *
     * @param Document $document
     * @return void
     */
    private function authorizeUser(Document $document): void
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Check if the document is already signed.
     *
     * @param Document $document
     * @return void
     */
    private function checkDocumentStatus(Document $document): void
    {
        if ($document->status === 'signed') {
            abort(400, 'Document already signed');
        }
    }

    /**
     * Create a new signature request.
     *
     * @param Request $request
     * @param Document $document
     * @return SignatureRequest
     */
    private function createNewSignatureRequest(Request $request, Document $document): SignatureRequest
    {
        $signatureRequest = new SignatureRequest();
        $signatureRequest->id = (string)Str::uuid();
        $signatureRequest->document_id = $document->id;
        $signatureRequest->requester_id = Auth::id();
        $signatureRequest->requested_user_id = $request->requested_user_id;
        $signatureRequest->status = 'pending';
        $signatureRequest->save();

        return $signatureRequest;
    }
}
