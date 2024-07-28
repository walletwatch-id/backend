<?php

namespace App\Policies;

use App\Models\SurveyAnswer;
use App\Models\User;

class SurveyAnswerPolicy
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
    public function view(User $user, SurveyAnswer $surveyAnswer): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyAnswer->result->user_id;
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
    public function update(User $user, SurveyAnswer $surveyAnswer): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyAnswer->result->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SurveyAnswer $surveyAnswer): bool
    {
        return $user->role === 'ADMIN' || $user->id === $surveyAnswer->result->user_id;
    }
}
