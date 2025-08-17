<?php

namespace App\Services\Octo;

use App\Models\Octo\Comment;
use App\Models\Octo\Connection;
use App\Models\Octo\Repository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class CommentService
{
    protected Connection $connection;

    public function __construct(User $user)
    {
        $this->connection = Connection::where('user_id', $user->id)->firstOrFail();
    }

    public function syncAllComments(): void
    {
        $repositories = Repository::where('octo_connection_id', $this->connection->id)->get();

        foreach ($repositories as $repository) {
            $this->syncRepositoryComments($repository);
        }
    }

    public function syncRepositoryComments(Repository $repository): void
    {
        // Sync issue comments
        $issueComments = $this->fetchIssueComments($repository->full_name);

        $this->saveComments(
            $issueComments,
            $repository,
            'issue'
        );

        // Sync PR comments
        $prComments = $this->fetchPullRequestComments($repository->full_name);
        $this->saveComments($prComments, $repository, 'pull_request');

        // Sync commit comments
        $commitComments = $this->fetchCommitComments($repository->full_name);
        $this->saveComments($commitComments, $repository, 'commit');
    }

    private function fetchIssueComments(string $repoFullName, ?int $issueNumber = null): array
    {
        $allComments = [];
        $perPage = 100;

        $endpoint = $issueNumber
            ? "/repos/{$repoFullName}/issues/{$issueNumber}/comments"
            : "/repos/{$repoFullName}/issues/comments";

        for ($page = 1; $page <= 100; $page++) {
            $response = $this->makeApiRequest($endpoint, [
                'per_page' => $perPage,
                'page' => $page,
                'sort' => 'updated',
                'direction' => 'desc'
            ]);

            $comments = $response->json();

            if (empty($comments)) {
                break;
            }

            $allComments = array_merge($allComments, $comments);

            if (count($comments) < $perPage) {
                break;
            }
        }

        return $allComments;
    }

    private function fetchPullRequestComments(string $repoFullName, ?int $pullNumber = null): array
    {
        $allComments = [];
        $perPage = 100;

        $endpoint = $pullNumber
            ? "/repos/{$repoFullName}/pulls/{$pullNumber}/comments"
            : "/repos/{$repoFullName}/pulls/comments";

        for ($page = 1; $page <= 100; $page++) {
            $response = $this->makeApiRequest($endpoint, [
                'per_page' => $perPage,
                'page' => $page,
                'sort' => 'updated',
                'direction' => 'desc'
            ]);

            $comments = $response->json();

            if (empty($comments)) {
                break;
            }

            $allComments = array_merge($allComments, $comments);

            if (count($comments) < $perPage) {
                break;
            }
        }

        return $allComments;
    }

    private function fetchCommitComments(string $repoFullName, ?string $commitSha = null): array
    {
        $allComments = [];
        $perPage = 100;

        $endpoint = $commitSha
            ? "/repos/{$repoFullName}/commits/{$commitSha}/comments"
            : "/repos/{$repoFullName}/comments";

        for ($page = 1; $page <= 100; $page++) {
            $response = $this->makeApiRequest($endpoint, [
                'per_page' => $perPage,
                'page' => $page,
            ]);

            $comments = $response->json();

            if (empty($comments)) {
                break;
            }

            $allComments = array_merge($allComments, $comments);

            if (count($comments) < $perPage) {
                break;
            }
        }

        return $allComments;
    }

    private function saveComments(array $comments, Repository $repository, string $type): void
    {
        foreach ($comments as $commentData) {
            Comment::updateOrCreate(
                [
                    'repository_id' => $repository->id,
                    'octo_id' => (string) $commentData['id'],
                ],
                [
                    'type' => $type,
                    'body' => $commentData['body'],
                    'author_login' => $commentData['user']['login'] ?? null,
                    'author_avatar_url' => $commentData['user']['avatar_url'] ?? null,
                    'created_at_github' => $commentData['created_at'] ? Carbon::parse($commentData['created_at']) : null,
                    'updated_at_github' => $commentData['updated_at'] ? Carbon::parse($commentData['updated_at']) : null,
                    'html_url' => $commentData['html_url'] ?? null,
                ]
            );
        }
    }

    private function makeApiRequest(string $url, array $params = [], string $method = 'GET', array $data = []): Response
    {
        try {
            $client = Http::withToken($this->connection->access_token)->timeout(30);
            
            $response = match (strtoupper($method)) {
                'POST' => $client->post("https://api.github.com{$url}", $data),
                'PUT' => $client->put("https://api.github.com{$url}", $data),
                'PATCH' => $client->patch("https://api.github.com{$url}", $data),
                'DELETE' => $client->delete("https://api.github.com{$url}"),
                default => $client->get("https://api.github.com{$url}", $params),
            };

            if ($response->failed()) {
                throw new \Exception("GitHub API request failed: " . $response->status() . " - " . $response->body());
            }

            return $response;
        } catch (RequestException $e) {
            throw new \Exception("GitHub API request failed: " . $e->getMessage());
        }
    }

    public static function forUser(User $user): self
    {
        return new self($user);
    }

    public function postIssueComment(string $repoFullName, int $issueNumber, string $commentBody): array
    {
        $response = $this->makeApiRequest(
            "/repos/{$repoFullName}/issues/{$issueNumber}/comments",
            [],
            'POST',
            ['body' => $commentBody]
        );

        return $response->json();
    }
}
