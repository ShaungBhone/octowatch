<?php

declare(strict_types=1);

use App\Models\Octo\Connection;
use App\Models\Octo\Issues;
use App\Models\Octo\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('issues upsert works with unique issue_id constraint', function () {
    $user = User::factory()->create();
    $connection = Connection::factory()->create(['user_id' => $user->id]);
    $repository = Repository::factory()->create(['octo_connection_id' => $connection->id]);

    $testData = [
        [
            'octo_connection_id' => $connection->id,
            'octo_repository_id' => $repository->id,
            'issue_id' => 123456789,
            'number' => 1,
            'title' => 'Test Issue',
            'body' => 'Test body',
            'state' => 'open',
            'author_login' => 'testuser',
            'author_avatar_url' => 'https://example.com/avatar.png',
            'labels' => '[]',
            'comments_count' => 0,
            'created_at_github' => now(),
            'updated_at_github' => now(),
            'closed_at_github' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ];

    // First insert should work
    Issues::upsert(
        $testData,
        ['issue_id'], // Unique key
        [
            'octo_connection_id', 'octo_repository_id', 'number', 'title', 'body', 'state', 'author_login',
            'author_avatar_url', 'labels', 'comments_count',
            'created_at_github', 'updated_at_github', 'closed_at_github', 'updated_at',
        ]
    );

    expect(Issues::where('issue_id', 123456789)->count())->toBe(1);

    // Update the title and try to upsert again
    $testData[0]['title'] = 'Updated Test Issue';
    $testData[0]['updated_at'] = now();

    Issues::upsert(
        $testData,
        ['issue_id'], // Unique key
        [
            'octo_connection_id', 'octo_repository_id', 'number', 'title', 'body', 'state', 'author_login',
            'author_avatar_url', 'labels', 'comments_count',
            'created_at_github', 'updated_at_github', 'closed_at_github', 'updated_at',
        ]
    );

    // Should still be only one record, but with updated title
    expect(Issues::where('issue_id', 123456789)->count())->toBe(1);
    expect(Issues::where('issue_id', 123456789)->first()->title)->toBe('Updated Test Issue');
});

test('issues upsert updates existing issues correctly', function () {
    $user = User::factory()->create();
    $connection = Connection::factory()->create(['user_id' => $user->id]);
    $repository = Repository::factory()->create(['octo_connection_id' => $connection->id]);

    // Create initial issue
    $issue = Issues::factory()->create([
        'octo_connection_id' => $connection->id,
        'octo_repository_id' => $repository->id,
        'issue_id' => 987654321,
        'title' => 'Original Title',
        'state' => 'open',
    ]);

    $updateData = [
        [
            'octo_connection_id' => $connection->id,
            'octo_repository_id' => $repository->id,
            'issue_id' => 987654321,
            'number' => $issue->number,
            'title' => 'Updated Title',
            'body' => 'Updated body',
            'state' => 'closed',
            'author_login' => $issue->author_login,
            'author_avatar_url' => $issue->author_avatar_url,
            'labels' => '[]',
            'comments_count' => 5,
            'created_at_github' => $issue->created_at_github,
            'updated_at_github' => now(),
            'closed_at_github' => now(),
            'created_at' => $issue->created_at,
            'updated_at' => now(),
        ],
    ];

    Issues::upsert(
        $updateData,
        ['issue_id'],
        [
            'octo_connection_id', 'octo_repository_id', 'number', 'title', 'body', 'state', 'author_login',
            'author_avatar_url', 'labels', 'comments_count',
            'created_at_github', 'updated_at_github', 'closed_at_github', 'updated_at',
        ]
    );

    $updatedIssue = Issues::where('issue_id', 987654321)->first();

    expect($updatedIssue->title)->toBe('Updated Title');
    expect($updatedIssue->state)->toBe('closed');
    expect($updatedIssue->comments_count)->toBe(5);
    expect($updatedIssue->closed_at_github)->not->toBeNull();

    // Ensure only one record exists
    expect(Issues::where('issue_id', 987654321)->count())->toBe(1);
});
