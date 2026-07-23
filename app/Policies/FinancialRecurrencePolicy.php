<?php

namespace App\Policies;

use App\Models\FinancialRecurrence;
use App\Models\User;

class FinancialRecurrencePolicy
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
    public function view(User $user, FinancialRecurrence $financialRecurrence): bool
    {
        return $user->id === $financialRecurrence->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FinancialRecurrence $financialRecurrence): bool
    {
        return $user->id === $financialRecurrence->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FinancialRecurrence $financialRecurrence): bool
    {
        return $user->id === $financialRecurrence->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FinancialRecurrence $financialRecurrence): bool
    {
        return $user->id === $financialRecurrence->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FinancialRecurrence $financialRecurrence): bool
    {
        return $user->id === $financialRecurrence->user_id;
    }
}
