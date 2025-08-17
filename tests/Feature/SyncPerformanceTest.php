<?php

use App\Jobs\{SyncRepositoriesJob, SyncIssuesJob, SyncCommentsJob};
use App\Models\User;
use App\Models\Octo\Connection;
use App\Services\Octo\CoordinatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('async sync jobs are dispatched correctly', function () {
    Queue::fake();
    
    $user = User::factory()->create();
    $connection = Connection::factory()->create(['user_id' => $user->id]);
    
    $coordinator = CoordinatorService::forUser($user);
    
    // Test individual async methods
    $coordinator->syncRepositoriesAsync();
    Queue::assertPushed(SyncRepositoriesJob::class);
    
    // For issues sync, we need to have at least one repository
    \App\Models\Octo\Repository::factory()->create([
        'octo_connection_id' => $connection->id
    ]);
    
    $coordinator->syncAllIssuesAsync();
    Queue::assertPushed(SyncIssuesJob::class);
    
    $coordinator->syncAllCommentsAsync();
    Queue::assertPushed(SyncCommentsJob::class);
});

test('chained sync jobs are queued correctly', function () {
    Queue::fake();
    
    $user = User::factory()->create();
    $connection = Connection::factory()->create(['user_id' => $user->id]);
    
    $coordinator = CoordinatorService::forUser($user);
    
    // Test chained jobs
    $coordinator->syncAllAsync();
    
    Queue::assertPushed(SyncRepositoriesJob::class);
    // Note: Chained jobs won't show up in the fake queue until the first job is processed
});

test('sync methods exist on coordinator service', function () {
    $user = User::factory()->create();
    $coordinator = CoordinatorService::forUser($user);
    
    expect(method_exists($coordinator, 'syncRepositoriesAsync'))->toBeTrue();
    expect(method_exists($coordinator, 'syncAllIssuesAsync'))->toBeTrue();
    expect(method_exists($coordinator, 'syncAllCommentsAsync'))->toBeTrue();
    expect(method_exists($coordinator, 'syncAllAsync'))->toBeTrue();
});

test('user has octo connections relationship', function () {
    $user = User::factory()->create();
    
    expect(method_exists($user, 'octoConnections'))->toBeTrue();
    expect($user->octoConnections())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});
