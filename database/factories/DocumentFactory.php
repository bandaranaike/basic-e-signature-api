<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => (string)Str::uuid(),
            'name' => $this->faker->word() . '.pdf',
            'file' => 'documents/' . $this->faker->uuid() . '.pdf',
            'user_id' => User::factory(),
        ];
    }
}
