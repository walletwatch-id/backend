<?php

namespace App\Broadcasting;

use App\Models\ChatSession;
use App\Models\User;

class ChatSessionChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, ChatSession $chatSession): array|bool
    {
        return $user->id === $chatSession->user_id;
    }
}
