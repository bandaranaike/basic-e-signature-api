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

        return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => $document,
        ], 201);
    }
}
