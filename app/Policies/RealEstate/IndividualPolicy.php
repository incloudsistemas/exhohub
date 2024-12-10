<?php

namespace App\Policies\RealEstate;

use App\Models\RealEstate\Individual;
use App\Models\System\User;
use Illuminate\Database\Eloquent\Builder;

class IndividualPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Imóveis à Venda e/ou Aluguel');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Individual $individual)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Imóveis à Venda e/ou Aluguel');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [IMB] Imóveis à Venda e/ou Aluguel');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Individual $individual)
    {
        if (!$user->hasPermissionTo('Editar [IMB] Imóveis à Venda e/ou Aluguel')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($individual): Builder {
                    return $query->where('id', $individual->property->user_id);
                })
                ->exists();
        }

        if ($individual->property->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Individual $individual)
    {
        if (!$user->hasPermissionTo('Deletar [IMB] Imóveis à Venda e/ou Aluguel')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($individual): Builder {
                    return $query->where('id', $individual->property->user_id);
                })
                ->exists();
        }

        if ($individual->property->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Individual $individual): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Individual $individual): bool
    {
        return false;
    }
}
