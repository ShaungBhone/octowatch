<?php

declare(strict_types=1);

use App\Models\Octo\Connection;
use App\Models\Octo\Issues;
use App\Models\Octo\Repository;
use App\Models\User;
use App\Services\Octo\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('can post a comment to GitHub and save locally', function () {
    // Create test data
    $user = User::factory()->create();
    $connection = Connection::factory()->create([
        'user_id' => $user->id,
        'username' => 'testuser',
        'access_token' => 'fake-token',
    ]);
    $repository = Repository::factory()->create([
        'octo_connection_id' => $connection->id,
        'full_name' => 'testuser/testrepo',
        'name' => 'testrepo',
    ]);
    $issue = Issues::factory()->create([
        'octo_repository_id' => $repository->id,
        'number' => 123,
        'title' => 'Test Issue',
    ]);

    // Mock GitHub API response
    Http::fake([
        'api.github.com/repos/testuser/testrepo/issues/123/comments' => Http::response([
            'id' => 12345,
            'body' => 'Test comment',
            'user' => [
                'login' => 'testuser',
                'avatar_url' => 'https://example.com/avatar.png',
            ],
            'html_url' => 'https://github.com/testuser/testrepo/issues/123#issuecomment-12345',
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
        ], 201),
    ]);

    // Test the service
    $commentService = CommentService::forUser($user);
    $result = $commentService->postIssueComment('testuser/testrepo', 123, 'Test comment');

    expect($result['id'])->toBe(12345);
    expect($result['body'])->toBe('Test comment');
    expect($result['user']['login'])->toBe('testuser');
});

it('handles GitHub API failures gracefully', function () {
    // Create test data
    $user = User::factory()->create();
    $connection = Connection::factory()->create([
        'user_id' => $user->id,
        'username' => 'testuser',
        'access_token' => 'fake-token',
    ]);

    // Mock GitHub API to fail
    Http::fake([
        'api.github.com/*' => Http::response('Unauthorized', 401),
    ]);

    $commentService = CommentService::forUser($user);

    try {
        $commentService->postIssueComment('testuser/testrepo', 123, 'Test comment');
        expect(false)->toBeTrue('Expected exception was not thrown');
    } catch (Exception $e) {
        expect($e->getMessage())->toContain('GitHub API request failed');
    }
});
