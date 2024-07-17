<?php

namespace App\Repositories;

use App\Models\ChatMessage;

interface AssistantFacade
{
    public function generateResponse(ChatMessage $chatMessage): ChatMessage;
}
