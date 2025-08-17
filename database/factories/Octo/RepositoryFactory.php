<?php

declare(strict_types=1);

namespace Database\Factories\Octo;

use App\Models\Octo\Connection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Octo\Repository>
 */
final class RepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->slug(2);
        $username = fake()->userName();

        return [
            'octo_connection_id' => Connection::factory(),
            'repo_id' => fake()->randomNumber(8),
            'name' => $name,
            'full_name' => "{$username}/{$name}",
            'description' => fake()->sentence(),
            'forks_count' => fake()->numberBetween(0, 1000),
            'stargazers_count' => fake()->numberBetween(0, 5000),
            'language' => fake()->randomElement(['PHP', 'JavaScript', 'Python', 'Ruby', 'Go']),
            'private' => fake()->boolean(20), // 20% chance of being private
            'open_issues_count' => fake()->numberBetween(0, 50),
            'watchers_count' => fake()->numberBetween(0, 100),
            'updated_at_github' => fake()->dateTimeThisYear()->format('Y-m-d H:i:s'),
        ];
    }
}
