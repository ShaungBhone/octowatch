<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Octo\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Octo\Comment>
 */
final class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'octo_id' => $this->faker->uuid(),
            'repository_id' => Repository::factory(),
            'body' => $this->faker->paragraph(),
            'author_login' => $this->faker->userName(),
            'author_avatar_url' => $this->faker->imageUrl(100, 100),
            'html_url' => $this->faker->url(),
            'created_at_github' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at_github' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'type' => $this->faker->randomElement(['issue', 'pull_request', 'commit']),
        ];
    }
}
