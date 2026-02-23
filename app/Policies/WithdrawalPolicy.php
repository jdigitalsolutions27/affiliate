<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

class WithdrawalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAffiliate();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin() || ($user->isAffiliate() && $user->affiliate?->id === $withdrawal->affiliate_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAffiliate();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin();
    }
}
