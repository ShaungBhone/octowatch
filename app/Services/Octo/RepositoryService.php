<?php

declare(strict_types=1);

namespace App\Services\Octo;

use App\Models\Octo\Connection;
use App\Models\Octo\Repository;
use App\Models\User;
use Carbon\Carbon;
use Exception;

final readonly class RepositoryService
{
    private Connection $connection;

    private ApiClientService $github;

    public function __construct(User $user)
    {
        $this->connection = Connection::where('user_id', $user->id)->firstOrFail();

        $this->github = new ApiClientService($this->connection);
    }

    public static function forUser(User $user): self
    {
        return new self($user);
    }

    public function syncRepositories(): void
    {
        $repositories = $this->fetchAllRepositories();

        // Process in chunks to avoid memory issues
        collect($repositories)
            ->chunk(50) // Process 50 repositories at a time
            ->each(function ($chunk): void {
                $this->processBatchRepositories($chunk);
            });
    }

    private function processBatchRepositories(\Illuminate\Support\Collection $repositoryChunk): void
    {
        $batchData = [];
        $now = now();

        foreach ($repositoryChunk as $repoData) {
            $repo = (object) $repoData;

            $batchData[] = [
                'octo_connection_id' => $this->connection->id,
                'repo_id' => (int) $repo->id,
                'name' => (string) $repo->name,
                'full_name' => (string) $repo->full_name,
                'description' => (string) ($repo->description ?? ''),
                'language' => (string) ($repo->language ?? ''),
                'private' => (bool) $repo->private,
                'stargazers_count' => (int) $repo->stargazers_count,
                'open_issues_count' => (int) $repo->open_issues_count,
                'forks_count' => (int) $repo->forks_count,
                'watchers_count' => (int) $repo->watchers_count,
                'updated_at_github' => Carbon::parse($repo->updated_at),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Use upsert for better performance
        Repository::upsert(
            $batchData,
            ['repo_id'], // Unique keys - repo_id is globally unique
            [
                'octo_connection_id', 'name', 'full_name', 'description', 'language', 'private',
                'stargazers_count', 'open_issues_count', 'forks_count',
                'watchers_count', 'updated_at_github', 'updated_at',
            ] // Columns to update
        );
    }

    private function fetchAllRepositories(): array
    {
        $repositories = [];
        $page = 1;
        $maxPages = 50; // Safety limit to prevent infinite loops

        do {
            $response = $this->github->get('/user/repos', [
                'sort' => 'updated',
                'per_page' => 100,
                'page' => $page,
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to fetch repositories: '.$response->body());
            }

            $data = $response->json();

            if (! is_array($data) || $data === []) {
                break;
            }

            $repositories = array_merge($repositories, $data);
            $page++;

            // Add a small delay to respect rate limits
            if (count($data) === 100) {
                usleep(100000); // 0.1 second delay
            }

        } while (count($data) === 100 && $page <= $maxPages);

        return $repositories;
    }
}
