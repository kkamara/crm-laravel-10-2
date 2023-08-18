<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Client;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class LogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->name;

        $user = User::inRandomOrder()->first();
        $client = Client::inRandomOrder()->first();

        return [
            'client_id' => $client->id,
            'user_created' => $user->id,
            'slug' => strtolower(Str::slug($title, '-')),
            'title' => $title,
            'description' => fake()->paragraph,
            'body' => fake()->paragraph,
            'notes' => fake()->paragraph,
        ];
    }
}
