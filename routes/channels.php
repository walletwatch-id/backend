<?php

use App\Broadcasting\ChatSessionChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat-sessions.{chat_session}', ChatSessionChannel::class);
