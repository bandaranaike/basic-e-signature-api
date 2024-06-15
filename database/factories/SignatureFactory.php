<?php

namespace Database\Factories;

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
            'public_id' => Str::uuid()->toString(),
            'file' => 'signatures/' . $this->faker->uuid() . '.png',
            'user_id' => User::factory(),
        ];
    }
}
