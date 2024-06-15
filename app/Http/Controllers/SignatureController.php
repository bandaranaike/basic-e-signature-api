<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->store('signatures', 'public');

        $signature = Signature::create([
            'file' => $path,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Signature uploaded successfully',
            'signature' => $signature,
        ], 201);
    }
}
