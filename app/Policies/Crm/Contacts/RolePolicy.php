<?php

namespace App\Policies\Crm\Contacts;

use App\Models\Crm\Contacts\Role;
use App\Models\System\User;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (
            $user->hasPermissionTo(permission: 'Visualizar [CRM] Contatos') &&
            $user->hasPermissionTo(permission: 'Visualizar [CRM] Tipos de Contatos')
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [CRM] Tipos de Contatos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [CRM] Tipos de Contatos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo(permission: 'Editar [CRM] Tipos de Contatos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo(permission: 'Deletar [CRM] Tipos de Contatos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
