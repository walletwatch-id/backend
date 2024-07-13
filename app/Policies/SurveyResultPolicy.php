<?php

namespace App\Policies;

use App\Models\SurveyResult;
use App\Models\User;

class SurveyResultPolicy
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
    public function view(User $user, SurveyResult $surveyResult): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyResult->user_id;
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
    public function update(User $user, SurveyResult $surveyResult): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyResult->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SurveyResult $surveyResult): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyResult->user_id;
    }
}
