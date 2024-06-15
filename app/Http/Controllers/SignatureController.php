<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadSignatureRequest;
use App\Models\Signature;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller
{
    public function upload(UploadSignatureRequest $request): JsonResponse
    {

        $file = $request->file('file');
        $path = $file->store('signatures', 'public');

        $signature = Signature::create([
            'file' => $path,
            'user_id' => Auth::id(),
        ]);

        return new JsonResponse([
            'message' => 'Signature uploaded successfully',
            'signature' => $signature,
        ], 201);
    }
}
