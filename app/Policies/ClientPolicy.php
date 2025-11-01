<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any clients.
     */
    public function viewAny(User $user): bool
    {
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can view the client.
     */
    public function view(User $user, Client $client): bool
    {
        // Admin peut voir tous les clients
        if ($user->admin !== null) {
            return true;
        }

        // Client ne peut voir que son propre profil
        return $user->client && $user->client->id === $client->id;
    }

    /**
     * Determine whether the user can create clients.
     */
    public function create(User $user): bool
    {
        // Seuls les admins peuvent créer des clients
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can update the client.
     */
    public function update(User $user, Client $client): bool
    {
        // Admin peut modifier tous les clients
        if ($user->admin !== null) {
            return true;
        }

        // Client ne peut modifier que son propre profil
        return $user->client && $user->client->id === $client->id;
    }

    /**
     * Determine whether the user can delete the client.
     */
    public function delete(User $user, Client $client): bool
    {
        // Seuls les admins peuvent supprimer des clients
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can restore the client.
     */
    public function restore(User $user, Client $client): bool
    {
        // Seuls les admins peuvent restaurer des clients
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can permanently delete the client.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        // Seuls les admins peuvent supprimer définitivement des clients
        return $user->admin !== null;
    }
}
