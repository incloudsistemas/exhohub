<?php

namespace App\Policies\Crm\Queues;

use App\Models\Crm\Queues\Queue;
use App\Models\System\User;

class QueuePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (
            $user->hasPermissionTo(permission: 'Visualizar [CRM] Negócios') &&
            $user->hasPermissionTo(permission: 'Visualizar [CRM] Filas')
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Queue $queue): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [CRM] Filas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [CRM] Filas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Queue $queue): bool
    {
        return $user->hasPermissionTo(permission: 'Editar [CRM] Filas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Queue $queue): bool
    {
        return $user->hasPermissionTo(permission: 'Deletar [CRM] Filas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Queue $queue): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Queue $queue): bool
    {
        return false;
    }
}
