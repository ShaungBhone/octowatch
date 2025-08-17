<?php

namespace Database\Factories\Octo;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Octo\Connection>
 */
class ConnectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'github_email' => fake()->email(),
            'github_id' => fake()->randomNumber(8),
            'access_token' => 'gho_' . fake()->sha256(),
            'refresh_token' => fake()->sha256(),
            'username' => fake()->userName(),
            'avatar_url' => fake()->imageUrl(200, 200),
        ];
    }
}
