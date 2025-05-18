<?php

namespace App\Policies;

use App\Models\Transacao;
use App\Models\User;

class TransacaoPolicy
{

    /**
     * Determine whether the user can revert the transaction.
     */
    public function reverter(User $user, Transacao $t): bool
    {
        return $user->id === $t->remetente_id || ($user->is_admin ?? false);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transacao $transacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transacao $transacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transacao $transacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Transacao $transacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transacao $transacao): bool
    {
        return false;
    }
}
