<?php

namespace App\Policies;

use App\Models\SurveyQuestion;
use App\Models\User;

class SurveyQuestionPolicy
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
    public function view(User $user, SurveyQuestion $surveyQuestion): bool
    {
        return $user->role === 'ADMIN' || $surveyQuestion->survey->is_active;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->role === 'ADMIN';
    }
}
