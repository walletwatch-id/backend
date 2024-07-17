<?php

namespace App\Listeners;

use App\Events\ChatMessageDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteChatMessage implements ShouldQueue
{
    /**
     * Handle chat message created event.
     */
    public function handle(ChatMessageDeleted $event): void
    {
        $event->chatMessage->delete();
    }
}
