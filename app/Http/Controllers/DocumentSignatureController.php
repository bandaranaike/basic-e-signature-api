<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestingSignatureRequest;
use App\Http\Requests\SignDocumentRequest;
use App\Models\Document;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentSignatureController extends Controller
{
    public function signDocument(SignDocumentRequest $request, $documentId): JsonResponse
    {
        $document = Document::findOrFail($documentId);
        $signature = Signature::findOrFail($request->signature_id);

        $documentPath = $this->getDocumentPath($document->file);
        if (!$documentPath) {
            return response()->json(['message' => 'Document file not found.'], 404);
        }

        $signaturePath = $this->getSignaturePath($signature->file);
        if (!$signaturePath) {
            return response()->json(['message' => 'Signature file not found.'], 404);
        }

        $pdf = Pdf::loadFile($documentPath);

        $html = $this->generateSignedDocumentHtml($pdf, $signaturePath);

        $pdf = Pdf::loadHTML($html);

        $newPdfPath = $this->saveSignedDocument($pdf, $document->file);
        $document->update(['file' => $newPdfPath]);

        $this->attachSignatureToDocument($document, $signature);

        return new JsonResponse(['message' => 'Document signed successfully'], 200);
    }

    public function sendSignatureRequest(RequestingSignatureRequest $request): JsonResponse
    {
        $document = Document::find($request->document_id);
        $recipient = User::find($request->user_id);

        if (!$document || !$recipient) {
            return new JsonResponse(['error' => 'Invalid document or user.'], 400);
        }

        $sender = Auth::user();

        if ($document->user_id != $sender->id) {
            return new JsonResponse(['error' => 'You do not have permission to send this document for signing.'], 403);
        }

        $signature = $this->createDocumentSignatureRequest($document, $recipient);

        return new JsonResponse(['message' => 'Signature request sent successfully.', 'document_signature' => $signature], 201);
    }

    private function getDocumentPath(string $file): ?string
    {
        $path = storage_path('app/public/' . $file);
        return file_exists($path) ? $path : null;
    }

    private function getSignaturePath(string $file): ?string
    {
        $path = storage_path('app/public/' . $file);
        return file_exists($path) ? $path : null;
    }

    private function generateSignedDocumentHtml($pdf, $signaturePath): string
    {
        $userName = Auth::user()->name;
        $currentDate = now()->format('Y-m-d H:i:s');

        $signatureImage = file_get_contents($signaturePath);
        $encodedImage = base64_encode($signatureImage);
        $imageDataUri = 'data:image/png;base64,' . $encodedImage;

        return view('pdf.signed_document', [
            'pdf' => $pdf->stream(),
            'imageDataUri' => $imageDataUri,
            'userName' => $userName,
            'currentDate' => $currentDate
        ])->render();
    }

    private function saveSignedDocument($pdf, string $originalFilePath): string
    {
        $newPdfPath = 'documents/signed_' . basename($originalFilePath);
        Storage::disk('public')->put($newPdfPath, $pdf->output());

        return $newPdfPath;
    }

    private function attachSignatureToDocument(Document $document, Signature $signature): void
    {
        $document->signatures()->attach($signature->id, [
            'signed_user_id' => Auth::id(),
            'signed_at' => now(),
        ]);
    }

    private function createDocumentSignatureRequest(Document $document, User $recipient): Signature
    {
        $signature = new Signature();
        $signature->document_id = $document->id;
        $signature->signed_user_id = $recipient->id;
        $signature->save();

        return $signature;
    }
}
