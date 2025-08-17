<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Octo\CommentService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SyncCommentsJob implements ShouldQueue
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
            Log::info('Starting comments sync', ['user_id' => $this->user->id]);

            $service = new CommentService($this->user);
            $service->syncAllComments();

            Log::info('Comments sync completed', ['user_id' => $this->user->id]);
        } catch (Exception $e) {
            Log::error('Comments sync failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
