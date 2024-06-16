<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentSignatureController;
use App\Http\Controllers\SignatureController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/document-signatures/create-requests', [DocumentSignatureController::class, 'createSignatureRequest']);

    Route::post('documents/{documentId}/sign', [SignatureController::class, 'signDocument'])->name('sign.document');
    Route::get('documents/{documentId}/verify-signature', [SignatureController::class, 'verifySignature'])->name('verify.signature');

    Route::post('/documents', [DocumentController::class, 'uploadDocument']);
    Route::get('/documents', [DocumentController::class, 'getUserDocuments']);
    Route::get('/documents/sign-requested', [DocumentController::class, 'getSignRequestedDocuments']);
});
