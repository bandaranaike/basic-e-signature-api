<?php

namespace Database\Factories;

use App\Models\SignatureRequest;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SignatureRequestFactory extends Factory
{

    public function definition()
    {
        return [
            'id' => (string)Str::uuid(),
            'document_id' => Document::factory(),
            'requester_id' => User::factory(),
            'requested_user_id' => User::factory(),
            'status' => 'pending',
        ];
    }
}
