<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentUploadRequest;
use App\Models\Document;
use App\Models\SignatureRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;

class DocumentController extends Controller
{
    /**
     * Upload a document
     *
     * @param DocumentUploadRequest $request
     * @return JsonResponse
     */
    public function uploadDocument(DocumentUploadRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $path = $file->storeAs('documents', Str::uuid() . '.' . $file->getClientOriginalExtension(), 'documents');

            $document = new Document();
            $document->id = (string)Str::uuid();
            $document->user_id = Auth::id();
            $document->title = $request->title;
            $document->file_path = $path;
            $document->status = 'pending';
            $document->save();
        } catch (\Exception $exception) {
            return new JsonResponse(['message' => 'An error occurred while uploading the document '], 500);
        }

        return new JsonResponse(['message' => 'Document uploaded successfully', 'document' => $document], 201);
    }

    /**
     * The list of all users documents
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserDocuments(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $documents = Document::where('user_id', Auth::id())->paginate($perPage);

            return new JsonResponse($documents);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while retrieving user documents', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * The list of documents which user has to sign
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSignRequestedDocuments(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $signatureRequests = SignatureRequest::where('requester_id', Auth::id())->with('document')->paginate($perPage);

            return new JsonResponse($signatureRequests);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while retrieving sign requested documents', 'message' => $e->getMessage()], 500);
        }
    }
}
