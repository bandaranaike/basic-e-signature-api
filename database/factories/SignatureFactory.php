<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Signature>
 */
class SignatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'document_id' => Document::factory(),
            'user_id' => User::factory(),
            'signature_file_path' => 'signatures/' . $this->faker->uuid . '.sig',
            'signature_hash' => base64_encode($this->faker->sha256),
        ];
    }
}
