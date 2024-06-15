<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentUploadRequest;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function upload(DocumentUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $document = Document::create([
            'name' => $request->input('name'),
            'file' => $path,
            'user_id' => Auth::id(),
        ]);

        return new JsonResponse([
            'message' => 'Document uploaded successfully',
            'document' => $document,
        ], 201);
    }

    public function userDocumentList(): JsonResponse
    {
        $documents = Auth::user()->documents()->with(['signatures' => function ($query) {
            $query->select('signatures.id', 'document_signature.signed_user_id', 'document_signature.signed_at')
                ->withPivot('signed_user_id', 'signed_at');
        }])->get();

        $documentsWithStatus = $documents->map(function ($document) {
            $document->signatures->each(function ($signature) {
                $signature->status = $signature->pivot->signed_at ? 'signed' : 'pending';
            });
            return $document;
        });

        return new JsonResponse($documentsWithStatus);
    }

}
