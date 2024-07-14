<?php

namespace App\Policies;

use App\Models\SurveyResultAnswer;
use App\Models\User;

class SurveyResultAnswerPolicy
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
    public function view(User $user, SurveyResultAnswer $surveyResultAnswer): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyResultAnswer->result->user_id;
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
    public function update(User $user, SurveyResultAnswer $surveyResultAnswer): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyResultAnswer->result->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SurveyResultAnswer $surveyResultAnswer): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyResultAnswer->result->user_id;
    }
}
