<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Octo\IssuesService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SyncIssuesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes

    public int $tries = 3;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        try {
            Log::info('Starting issues sync', ['user_id' => $this->user->id]);

            $service = new IssuesService($this->user);
            $service->syncAllIssues();

            Log::info('Issues sync completed', ['user_id' => $this->user->id]);
        } catch (Exception $e) {
            Log::error('Issues sync failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
