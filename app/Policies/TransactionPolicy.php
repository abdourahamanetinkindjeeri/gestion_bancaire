<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any transactions.
     */
    public function viewAny(User $user): bool
    {
        return $user->admin !== null || $user->client !== null;
    }

    /**
     * Determine whether the user can view the transaction.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Admin peut voir toutes les transactions
        if ($user->admin !== null) {
            return true;
        }

        // Client ne peut voir que les transactions de ses propres comptes
        return $user->client && $user->client->id === $transaction->compte->client_id;
    }

    /**
     * Determine whether the user can create transactions.
     */
    public function create(User $user): bool
    {
        // Seuls les admins peuvent créer des transactions manuellement
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can update the transaction.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Seuls les admins peuvent modifier des transactions
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can delete the transaction.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // Seuls les admins peuvent supprimer des transactions
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can restore the transaction.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        // Seuls les admins peuvent restaurer des transactions
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can permanently delete the transaction.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        // Seuls les admins peuvent supprimer définitivement des transactions
        return $user->admin !== null;
    }
}
