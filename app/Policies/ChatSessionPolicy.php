<?php

namespace App\Policies;

use App\Models\ChatSession;
use App\Models\User;

class ChatSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChatSession $chatSession): bool
    {
        return $user->role === 'ADMIN' || $user->id === $chatSession->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChatSession $chatSession): bool
    {
        return $user->role === 'ADMIN' || $user->id === $chatSession->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChatSession $chatSession): bool
    {
        return $user->role === 'ADMIN' || $user->id === $chatSession->user_id;
    }
}
