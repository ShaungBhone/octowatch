<?php

declare(strict_types=1);

namespace Database\Factories\Octo;

use App\Models\Octo\Connection;
use App\Models\Octo\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Octo\Issues>
 */
final class IssuesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'octo_connection_id' => Connection::factory(),
            'octo_repository_id' => Repository::factory(),
            'issue_id' => fake()->randomNumber(8),
            'number' => fake()->numberBetween(1, 9999),
            'title' => fake()->sentence(6),
            'body' => fake()->paragraph(5),
            'state' => fake()->randomElement(['open', 'closed']),
            'author_login' => fake()->userName(),
            'author_avatar_url' => fake()->imageUrl(100, 100),
            'labels' => fake()->randomElements(['bug', 'enhancement', 'question', 'documentation', 'good first issue'], fake()->numberBetween(0, 3)),
            'assignees' => fake()->optional()->userName(),
            'comments_count' => fake()->numberBetween(0, 25),
            'created_at_github' => fake()->dateTimeThisYear()->format('Y-m-d H:i:s'),
            'updated_at_github' => fake()->dateTimeThisYear()->format('Y-m-d H:i:s'),
            'closed_at_github' => fake()->optional(0.3)->passthrough(fake()->dateTimeThisYear()->format('Y-m-d H:i:s')),
        ];
    }
}
