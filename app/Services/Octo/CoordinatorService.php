<?php

declare(strict_types=1);

namespace App\Services\Octo;

use App\Jobs\SyncCommentsJob;
use App\Jobs\SyncIssuesJob;
use App\Jobs\SyncRepositoriesJob;
use App\Models\Octo\Connection;
use App\Models\Octo\Repository;
use App\Models\User;
use Exception;

final readonly class CoordinatorService
{
    public function __construct(
        private User $user
    ) {}

    public static function forUser(User $user): self
    {
        return new self($user);
    }

    public function syncRepositories(): void
    {
        $repositoryService = new RepositoryService($this->user);

        $repositoryService->syncRepositories();
    }

    public function syncRepositoriesAsync(): void
    {
        SyncRepositoriesJob::dispatch($this->user);
    }

    public function syncAllIssues(): void
    {
        // Check if repositories are synced, if not sync them first
        $this->ensureRepositoriesAreSynced();

        $this->doSyncAllIssues();
    }

    public function syncAllIssuesAsync(): void
    {
        // Ensure repositories are synced first
        $this->ensureRepositoriesAreSynced();

        SyncIssuesJob::dispatch($this->user);
    }

    public function syncAllComments(): void
    {
        $commentService = new CommentService($this->user);

        $commentService->syncAllComments();
    }

    public function syncAllCommentsAsync(): void
    {
        SyncCommentsJob::dispatch($this->user);
    }

    public function syncRepositoriesAndIssues(): void
    {
        $this->syncRepositories();
        $this->doSyncAllIssues();
    }

    public function syncRepositoriesAndIssuesAsync(): void
    {
        // Chain jobs - sync repositories first, then issues
        SyncRepositoriesJob::dispatch($this->user)
            ->chain([
                new SyncIssuesJob($this->user),
            ]);
    }

    public function syncRepositoriesWithComments(): void
    {
        $this->syncRepositories();

        $this->syncAllComments();
    }

    public function syncRepositoriesWithCommentsAsync(): void
    {
        // Chain jobs - sync repositories first, then comments
        SyncRepositoriesJob::dispatch($this->user)
            ->chain([
                new SyncCommentsJob($this->user),
            ]);
    }

    public function syncAllAsync(): void
    {
        // Chain all sync jobs for complete synchronization
        SyncRepositoriesJob::dispatch($this->user)
            ->chain([
                new SyncIssuesJob($this->user),
                new SyncCommentsJob($this->user),
            ]);
    }

    public function syncIssuesAndCommentsAsync(): void
    {
        // Ensure repositories are synced first
        $this->ensureRepositoriesAreSynced();

        // Chain issues and comments jobs
        SyncIssuesJob::dispatch($this->user)
            ->chain([
                new SyncCommentsJob($this->user),
            ]);
    }

    /**
     * Internal method to sync issues without checking for repositories
     */
    private function doSyncAllIssues(): void
    {
        $issueService = new IssuesService($this->user);

        $issueService->syncAllIssues();
    }

    /**
     * Ensure repositories are synced before syncing issues.
     * This method checks if any repositories exist for the user's connection,
     * and if not, it syncs them first.
     */
    private function ensureRepositoriesAreSynced(): void
    {
        $connection = Connection::where('user_id', $this->user->id)->first();

        if (! $connection) {
            throw new Exception('No GitHub connection found for user.');
        }

        $repositoryCount = Repository::where('octo_connection_id', $connection->id)->count();

        // If no repositories exist, sync them first
        if ($repositoryCount === 0) {
            $this->syncRepositories();
        }
    }
}
