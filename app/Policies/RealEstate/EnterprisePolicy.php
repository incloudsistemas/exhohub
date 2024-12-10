<?php

namespace App\Policies\RealEstate;

use App\Models\RealEstate\Enterprise;
use App\Models\System\User;
use Illuminate\Database\Eloquent\Builder;

class EnterprisePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Lançamentos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Enterprise $enterprise)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Lançamentos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [IMB] Lançamentos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Enterprise $enterprise)
    {
        if (!$user->hasPermissionTo('Editar [IMB] Lançamentos')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($enterprise): Builder {
                    return $query->where('id', $enterprise->property->user_id);
                })
                ->exists();
        }

        if ($enterprise->property->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enterprise $enterprise)
    {
        if (!$user->hasPermissionTo('Deletar [IMB] Lançamentos')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($enterprise): Builder {
                    return $query->where('id', $enterprise->property->user_id);
                })
                ->exists();
        }

        if ($enterprise->property->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Enterprise $enterprise): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Enterprise $enterprise): bool
    {
        return false;
    }
}
