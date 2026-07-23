<?php

namespace App\Policies;

use App\Models\FinancialTag;
use App\Models\User;

class FinancialTagPolicy
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
    public function view(User $user, FinancialTag $financialTag): bool
    {
        return $user->id === $financialTag->user_id;
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
    public function update(User $user, FinancialTag $financialTag): bool
    {
        return $user->id === $financialTag->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FinancialTag $financialTag): bool
    {
        return $user->id === $financialTag->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FinancialTag $financialTag): bool
    {
        return $user->id === $financialTag->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FinancialTag $financialTag): bool
    {
        return $user->id === $financialTag->user_id;
    }
}
