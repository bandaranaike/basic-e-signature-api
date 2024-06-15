<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentSignatureController;
use App\Http\Controllers\SignatureController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->post('/documents/upload', [DocumentController::class, 'upload']);

Route::middleware(['auth:sanctum'])->post('/signatures/upload', [SignatureController::class, 'upload']);
Route::middleware(['auth:sanctum'])->post('/documents/{document}/sign', [DocumentSignatureController::class, 'signDocument']);
