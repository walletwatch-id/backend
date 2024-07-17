<?php

namespace App\Listeners;

use App\Events\ChatMessageDeleted;
use App\Events\ChatMessageUpdated;
use App\Models\ChatMessage;
use App\Repositories\AssistantFacade;
use Illuminate\Contracts\Queue\ShouldQueue;

class RespondUpdatedChatMessage implements ShouldQueue
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
    public function handle(ChatMessageUpdated $event): void
    {
        $chatMessage = $event->chatMessage->fresh();
        $hash = md5($chatMessage->message);

        if ($chatMessage->hash !== $hash) {
            $chatMessage->hash = $hash;
            $chatMessage->save();

            $nextChatMessages = ChatMessage::where('session_id', $event->chatMessage->session_id)
                ->where('created_at', '>', $event->chatMessage->created_at)
                ->get();

            foreach ($nextChatMessages as $nextChatMessage) {
                broadcast(new ChatMessageDeleted($nextChatMessage));
            }

            $this->assistantFacade->generateResponse($event->chatMessage);
        }
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(ChatMessageUpdated $event): bool
    {
        return $event->chatMessage->sender === 'USER';
    }
}
