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
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = fake()->unique()->company;

        return [
            'slug' => strtolower(Str::slug($company, '-')),
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'user_created' => function() {
                return User::factory()->create()->id;
            },
            'company' => $company,
            'contact_number' => fake()->phonenumber,
            'building_number' => fake()->buildingnumber,
            'city' => fake()->city,
            'postcode' => fake()->postcode,
            'email' => fake()->unique()->safeEmail,
            'street_name' => fake()->StreetAddress,
        ];
    }
}
