<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Signature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentSignatureController extends Controller
{
    public function signDocument(Request $request, $documentId): JsonResponse
    {
        $request->validate([
            'signature_id' => 'required|exists:signatures,id',
        ]);

        $document = Document::findOrFail($documentId);
        $signature = Signature::findOrFail($request->signature_id);

        $document->signatures()->attach($signature->id, [
            'signed_user_id' => Auth::id(),
            'signed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Document signed successfully',
        ], 200);
    }
}
