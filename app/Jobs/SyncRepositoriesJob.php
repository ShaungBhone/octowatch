<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Octo\RepositoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncRepositoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        try {
            Log::info('Starting repository sync', ['user_id' => $this->user->id]);
            
            $service = new RepositoryService($this->user);
            $service->syncRepositories();
            
            Log::info('Repository sync completed', ['user_id' => $this->user->id]);
        } catch (\Exception $e) {
            Log::error('Repository sync failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
