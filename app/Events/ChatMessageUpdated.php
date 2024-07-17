<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ChatMessage $chatMessage,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat-sessions.'.$this->chatMessage->session_id);
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return $this->chatMessage->sender === 'BOT';
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'chat-message.updated';
    }
}
