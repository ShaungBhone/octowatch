<?php

declare(strict_types=1);

use App\Models\Octo\Connection;
use App\Models\Octo\Repository;
use App\Models\User;
use App\Services\Octo\RepositoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('repository upsert works with unique repo_id constraint', function () {
    // Create test data
    $user = User::factory()->create();
    $connection = Connection::factory()->create([
        'user_id' => $user->id,
        'username' => 'testuser',
        'access_token' => 'fake-token',
    ]);

    // Mock GitHub API response for repositories
    Http::fake([
        'api.github.com/user/repos*' => Http::response([
            [
                'id' => 123456,
                'name' => 'test-repo',
                'full_name' => 'testuser/test-repo',
                'description' => 'A test repository',
                'language' => 'PHP',
                'private' => false,
                'stargazers_count' => 10,
                'open_issues_count' => 2,
                'forks_count' => 5,
                'watchers_count' => 8,
                'updated_at' => '2023-12-01T12:00:00Z',
            ],
            [
                'id' => 789012,
                'name' => 'another-repo',
                'full_name' => 'testuser/another-repo',
                'description' => 'Another test repository',
                'language' => 'JavaScript',
                'private' => true,
                'stargazers_count' => 20,
                'open_issues_count' => 0,
                'forks_count' => 3,
                'watchers_count' => 15,
                'updated_at' => '2023-12-02T12:00:00Z',
            ],
        ], 200),
    ]);

    // Test repository sync
    $service = new RepositoryService($user);
    $service->syncRepositories();

    // Verify repositories were created
    expect(Repository::count())->toBe(2);

    $repo1 = Repository::where('repo_id', 123456)->first();
    expect($repo1)->not->toBeNull();
    expect($repo1->name)->toBe('test-repo');
    expect($repo1->full_name)->toBe('testuser/test-repo');
    expect($repo1->octo_connection_id)->toBe($connection->id);

    $repo2 = Repository::where('repo_id', 789012)->first();
    expect($repo2)->not->toBeNull();
    expect($repo2->name)->toBe('another-repo');
    expect($repo2->private)->toBeTrue();
});

test('repository upsert updates existing repositories correctly', function () {
    // Create test data
    $user = User::factory()->create();
    $connection = Connection::factory()->create([
        'user_id' => $user->id,
        'username' => 'testuser',
        'access_token' => 'fake-token',
    ]);

    // Create an existing repository
    $existingRepo = Repository::factory()->create([
        'octo_connection_id' => $connection->id,
        'repo_id' => 123456,
        'name' => 'old-name',
        'full_name' => 'testuser/old-name',
        'stargazers_count' => 5,
    ]);

    // Mock GitHub API response with updated data
    Http::fake([
        'api.github.com/user/repos*' => Http::response([
            [
                'id' => 123456, // Same repo_id
                'name' => 'updated-name',
                'full_name' => 'testuser/updated-name',
                'description' => 'Updated repository',
                'language' => 'PHP',
                'private' => false,
                'stargazers_count' => 25, // Updated count
                'open_issues_count' => 1,
                'forks_count' => 8,
                'watchers_count' => 20,
                'updated_at' => '2023-12-03T12:00:00Z',
            ],
        ], 200),
    ]);

    // Test repository sync
    $service = new RepositoryService($user);
    $service->syncRepositories();

    // Verify only one repository exists (updated, not duplicated)
    expect(Repository::count())->toBe(1);

    $updatedRepo = Repository::where('repo_id', 123456)->first();
    expect($updatedRepo)->not->toBeNull();
    expect($updatedRepo->id)->toBe($existingRepo->id); // Same database record
    expect($updatedRepo->name)->toBe('updated-name'); // Updated name
    expect($updatedRepo->stargazers_count)->toBe(25); // Updated count
    expect($updatedRepo->octo_connection_id)->toBe($connection->id); // Connection updated too
});

test('repository upsert handles multiple connections with same repo_id', function () {
    // Create two different users with different connections
    $user1 = User::factory()->create();
    $connection1 = Connection::factory()->create([
        'user_id' => $user1->id,
        'username' => 'user1',
        'access_token' => 'fake-token-1',
    ]);

    $user2 = User::factory()->create();
    $connection2 = Connection::factory()->create([
        'user_id' => $user2->id,
        'username' => 'user2',
        'access_token' => 'fake-token-2',
    ]);

    // Mock GitHub API responses for both users
    Http::fake([
        'api.github.com/user/repos*' => Http::response([
            [
                'id' => 123456, // Same repo_id for both users
                'name' => 'shared-repo',
                'full_name' => 'owner/shared-repo',
                'description' => 'A shared repository',
                'language' => 'PHP',
                'private' => false,
                'stargazers_count' => 10,
                'open_issues_count' => 2,
                'forks_count' => 5,
                'watchers_count' => 8,
                'updated_at' => '2023-12-01T12:00:00Z',
            ],
        ], 200),
    ]);

    // First user syncs repositories
    $service1 = new RepositoryService($user1);
    $service1->syncRepositories();

    expect(Repository::count())->toBe(1);
    $repo1 = Repository::where('repo_id', 123456)->first();
    expect($repo1->octo_connection_id)->toBe($connection1->id);

    // Second user tries to sync the same repository
    $service2 = new RepositoryService($user2);
    $service2->syncRepositories();

    // Should still only have one repository record
    // The last sync overwrites the connection_id (this is the expected behavior)
    expect(Repository::count())->toBe(1);
    $repo = Repository::where('repo_id', 123456)->first();
    expect($repo->octo_connection_id)->toBe($connection2->id); // Updated to second connection
});
