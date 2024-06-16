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
            'id' => (string)Str::uuid(),
            'title' => $this->faker->word() . '.pdf',
            'file_path' => 'documents/' . $this->faker->uuid() . '.pdf',
            'user_id' => User::factory(),
        ];
    }
}
