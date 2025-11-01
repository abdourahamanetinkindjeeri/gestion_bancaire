<?php

namespace App\Policies;

use App\Models\Compte;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComptePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any comptes.
     */
    public function viewAny(User $user): bool
    {
        return $user->admin !== null || $user->client !== null;
    }

    /**
     * Determine whether the user can view the compte.
     */
    public function view(User $user, Compte $compte): bool
    {
        // Admin peut voir tous les comptes
        if ($user->admin !== null) {
            return true;
        }

        // Client ne peut voir que ses propres comptes
        return $user->client && $user->client->id === $compte->client_id;
    }

    /**
     * Determine whether the user can create comptes.
     */
    public function create(User $user): bool
    {
        // Seuls les admins peuvent créer des comptes
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can update the compte.
     */
    public function update(User $user, Compte $compte): bool
    {
        // Admin peut modifier tous les comptes
        if ($user->admin !== null) {
            return true;
        }

        // Client ne peut modifier que ses propres comptes (avec restrictions)
        return $user->client && $user->client->id === $compte->client_id;
    }

    /**
     * Determine whether the user can delete the compte.
     */
    public function delete(User $user, Compte $compte): bool
    {
        // Seuls les admins peuvent supprimer des comptes
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can restore the compte.
     */
    public function restore(User $user, Compte $compte): bool
    {
        // Seuls les admins peuvent restaurer des comptes
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can permanently delete the compte.
     */
    public function forceDelete(User $user, Compte $compte): bool
    {
        // Seuls les admins peuvent supprimer définitivement des comptes
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can bloquer the compte.
     */
    public function bloquer(User $user, Compte $compte): bool
    {
        // Seuls les admins peuvent bloquer des comptes
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can debloquer the compte.
     */
    public function debloquer(User $user, Compte $compte): bool
    {
        // Seuls les admins peuvent débloquer des comptes
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can archiver the compte.
     */
    public function archiver(User $user, Compte $compte): bool
    {
        // Seuls les admins peuvent archiver des comptes
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can desarchiver the compte.
     */
    public function desarchiver(User $user, Compte $compte): bool
    {
        // Seuls les admins peuvent désarchiver des comptes
        return $user->admin !== null;
    }
}
