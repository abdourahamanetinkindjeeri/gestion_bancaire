<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any admins.
     */
    public function viewAny(User $user): bool
    {
        // Seuls les admins peuvent voir la liste des admins
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can view the admin.
     */
    public function view(User $user, Admin $admin): bool
    {
        // Admin peut voir tous les autres admins
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can create admins.
     */
    public function create(User $user): bool
    {
        // Seuls les admins peuvent créer d'autres admins
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can update the admin.
     */
    public function update(User $user, Admin $admin): bool
    {
        // Admin peut modifier tous les autres admins
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can delete the admin.
     */
    public function delete(User $user, Admin $admin): bool
    {
        // Seuls les admins peuvent supprimer d'autres admins
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can restore the admin.
     */
    public function restore(User $user, Admin $admin): bool
    {
        // Seuls les admins peuvent restaurer d'autres admins
        return $user->admin !== null;
    }

    /**
     * Determine whether the user can permanently delete the admin.
     */
    public function forceDelete(User $user, Admin $admin): bool
    {
        // Seuls les admins peuvent supprimer définitivement d'autres admins
        return $user->admin !== null;
    }
}
