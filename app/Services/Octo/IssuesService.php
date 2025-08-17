<?php

declare(strict_types=1);

namespace App\Services\Octo;

use App\Models\Octo\Connection;
use App\Models\Octo\Issues;
use App\Models\Octo\Repository;
use App\Models\User;
use Carbon\Carbon;
use Exception;

final readonly class IssuesService
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

    public function syncAllIssues(): void
    {
        $repositories = Repository::where(
            'octo_connection_id',
            $this->connection->id
        )->get();

        foreach ($repositories as $repository) {
            $this->syncRepositoryIssues($repository);
        }
    }

    public function syncRepositoryIssues(Repository $repository): void
    {
        $issues = $this->fetchAllIssues($repository->full_name);

        // Process in chunks to improve memory usage and database performance
        collect($issues)
            ->chunk(50) // Process 50 issues at a time
            ->each(function ($chunk) use ($repository): void {
                $this->processBatchIssues($chunk, $repository);
            });
    }

    public function fetchIssuesByState(Repository $repository, string $state = 'open'): array
    {
        return $this->fetchAllIssues($repository->full_name, $state);
    }

    public function fetchIssuesByLabel(Repository $repository, string $label): array
    {
        $response = $this->github->get("/repos/{$repository->full_name}/issues", [
            'labels' => $label,
            'state' => 'all',
            'per_page' => 100,
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to fetch issues by label: '.$response->body());
        }

        return $response->json();
    }

    private function processBatchIssues(\Illuminate\Support\Collection $issueChunk, Repository $repository): void
    {
        $batchData = [];
        $now = now();

        foreach ($issueChunk as $issueData) {
            $batchData[] = [
                'octo_connection_id' => $this->connection->id,
                'octo_repository_id' => $repository->id,
                'issue_id' => $issueData['id'],
                'number' => $issueData['number'],
                'title' => $issueData['title'],
                'body' => $issueData['body'] ?? '',
                'state' => $issueData['state'],
                'author_login' => $issueData['user']['login'] ?? null,
                'author_avatar_url' => $issueData['user']['avatar_url'] ?? null,
                'labels' => json_encode($issueData['labels']),
                'comments_count' => $issueData['comments'] ?? 0,
                'created_at_github' => Carbon::parse($issueData['created_at']),
                'updated_at_github' => Carbon::parse($issueData['updated_at']),
                'closed_at_github' => $issueData['closed_at'] ? Carbon::parse($issueData['closed_at']) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Use upsert for better performance
        Issues::upsert(
            $batchData,
            ['issue_id'], // Unique keys - GitHub issue IDs are globally unique
            [
                'octo_connection_id', 'octo_repository_id', 'number', 'title', 'body', 'state', 'author_login',
                'author_avatar_url', 'labels', 'comments_count',
                'created_at_github', 'updated_at_github', 'closed_at_github', 'updated_at',
            ] // Columns to update
        );
    }

    private function fetchAllIssues(string $repoFullName, string $state = 'all'): array
    {
        $allIssues = [];
        $page = 1;
        $perPage = 100;
        $maxPages = 50; // Safety limit

        do {
            $response = $this->github->get("/repos/{$repoFullName}/issues", [
                'state' => $state,
                'per_page' => $perPage,
                'page' => $page,
                'sort' => 'updated',
                'direction' => 'desc',
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to fetch issues: '.$response->body());
            }

            $issues = $response->json();

            if (empty($issues)) {
                break;
            }

            // Filter out pull requests (GitHub API includes PRs in issues endpoint)
            $actualIssues = array_filter($issues, fn (array $issue): bool => ! isset($issue['pull_request']));

            $allIssues = array_merge($allIssues, $actualIssues);
            $page++;

            // Add rate limiting delay
            if (count($issues) === $perPage) {
                usleep(100000); // 0.1 second delay
            }

        } while (count($issues) === $perPage && $page <= $maxPages);

        return $allIssues;
    }
}
