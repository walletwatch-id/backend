<?php

namespace App\Listeners;

use App\Events\ChatMessageCreated;
use App\Repositories\AssistantFacade;
use Illuminate\Contracts\Queue\ShouldQueue;

class RespondCreatedChatMessage implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected AssistantFacade $assistantFacade,
    ) {}

    /**
     * Handle chat message created event.
     */
    public function handle(ChatMessageCreated $event): void
    {
        $this->assistantFacade->generateResponse($event->chatMessage);
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(ChatMessageCreated $event): bool
    {
        return $event->chatMessage->sender === 'USER';
    }
}
