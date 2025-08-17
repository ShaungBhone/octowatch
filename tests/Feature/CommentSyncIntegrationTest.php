<?php

declare(strict_types=1);

use App\Jobs\{SyncRepositoriesJob, SyncIssuesJob, SyncCommentsJob};
use App\Models\User;
use App\Models\Octo\Connection;
use App\Services\Octo\CoordinatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('coordinator can sync repositories with comments async', function () {
    Queue::fake();
    
    $user = User::factory()->create();
    $coordinator = CoordinatorService::forUser($user);

    $coordinator->syncRepositoriesWithCommentsAsync();

    Queue::assertPushed(SyncRepositoriesJob::class);
    Queue::assertPushedWithChain(SyncRepositoriesJob::class, [
        SyncCommentsJob::class,
    ]);
});

test('coordinator can sync issues with comments async', function () {
    Queue::fake();
    
    $user = User::factory()->create();
    $connection = Connection::factory()->for($user)->create();
    \App\Models\Octo\Repository::factory()->create([
        'octo_connection_id' => $connection->id
    ]);
    
    $coordinator = CoordinatorService::forUser($user);

    $coordinator->syncIssuesAndCommentsAsync();

    Queue::assertPushed(SyncIssuesJob::class);
    Queue::assertPushedWithChain(SyncIssuesJob::class, [
        SyncCommentsJob::class,
    ]);
});

test('coordinator can sync all data async including comments', function () {
    Queue::fake();
    
    $user = User::factory()->create();
    $coordinator = CoordinatorService::forUser($user);

    $coordinator->syncAllAsync();

    Queue::assertPushed(SyncRepositoriesJob::class);
    Queue::assertPushedWithChain(SyncRepositoriesJob::class, [
        SyncIssuesJob::class,
        SyncCommentsJob::class,
    ]);
});

test('sync jobs are chained in correct order', function () {
    Queue::fake();
    
    $user = User::factory()->create();
    $coordinator = CoordinatorService::forUser($user);

    $coordinator->syncAllAsync();

    // Verify that the repositories job is dispatched first
    Queue::assertPushed(SyncRepositoriesJob::class, function ($job) use ($user) {
        return $job->user->id === $user->id;
    });

    // The issues and comments jobs should be chained to the repositories job
    Queue::assertPushedWithChain(SyncRepositoriesJob::class, [
        SyncIssuesJob::class,
        SyncCommentsJob::class,
    ]);
});

test('new comment sync methods exist on coordinator service', function () {
    $user = User::factory()->create();
    $coordinator = CoordinatorService::forUser($user);
    
    expect(method_exists($coordinator, 'syncRepositoriesWithCommentsAsync'))->toBeTrue();
    expect(method_exists($coordinator, 'syncIssuesAndCommentsAsync'))->toBeTrue();
});
