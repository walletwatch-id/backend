<?php

namespace App\Policies;

use App\Models\Statistic;
use App\Models\User;

class StatisticPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Statistic $statistic): bool
    {
        return $user->role === 'ADMIN' || $user->id === $statistic->user_id;
    }
}
