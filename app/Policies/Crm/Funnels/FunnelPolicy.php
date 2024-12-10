<?php

namespace App\Policies\Crm\Funnels;

use App\Models\Crm\Funnels\Funnel;
use App\Models\System\User;

class FunnelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (
            $user->hasPermissionTo(permission: 'Visualizar [CRM] Negócios') &&
            $user->hasPermissionTo(permission: 'Visualizar [CRM] Funis de Negócios')
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Funnel $funnel): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [CRM] Funis de Negócios');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [CRM] Funis de Negócios');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Funnel $funnel): bool
    {
        return $user->hasPermissionTo(permission: 'Editar [CRM] Funis de Negócios');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Funnel $funnel): bool
    {
        return $user->hasPermissionTo(permission: 'Deletar [CRM] Funis de Negócios');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Funnel $funnel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Funnel $funnel): bool
    {
        return false;
    }
}
