<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCommentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CommentCreated $event): void
    {
        // For now, we'll just log that a comment was created
        // You can implement notification logic here later
        logger('Comment created', [
            'comment_id' => $event->comment->id,
            'repository_id' => $event->comment->repository_id,
            'type' => $event->comment->type,
            'author' => $event->comment->author_login,
        ]);
    }
}
