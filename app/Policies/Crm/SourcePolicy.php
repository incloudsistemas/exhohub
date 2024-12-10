<?php

namespace App\Policies\Crm;

use App\Models\Crm\Source;
use App\Models\System\User;

class SourcePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [CRM] Origens dos Contatos/Negócios');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Source $source): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [CRM] Origens dos Contatos/Negócios');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [CRM] Origens dos Contatos/Negócios');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Source $source): bool
    {
        return $user->hasPermissionTo(permission: 'Editar [CRM] Origens dos Contatos/Negócios');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Source $source): bool
    {
        return $user->hasPermissionTo(permission: 'Deletar [CRM] Origens dos Contatos/Negócios');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Source $source): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Source $source): bool
    {
        return false;
    }
}
