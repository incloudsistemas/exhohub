<?php

namespace App\Policies\Crm\Business;

use App\Models\Crm\Business\Business;
use App\Models\System\User;
use Illuminate\Database\Eloquent\Builder;

class BusinessPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [CRM] Negócios');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Business $business): bool
    {
        if (!$user->hasPermissionTo('Visualizar [CRM] Negócios')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($business): Builder {
                    return $query->where('id', $business->user_id);
                })
                ->exists();
        }

        if ($business->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [CRM] Negócios');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Business $business): bool
    {
        if (!$user->hasPermissionTo('Editar [CRM] Negócios')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($business): Builder {
                    return $query->where('id', $business->user_id);
                })
                ->exists();
        }

        if ($business->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Business $business): bool
    {
        if (!$user->hasPermissionTo('Deletar [CRM] Negócios')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($business): Builder {
                    return $query->where('id', $business->user_id);
                })
                ->exists();
        }

        if ($business->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Business $business): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Business $business): bool
    {
        return false;
    }
}
