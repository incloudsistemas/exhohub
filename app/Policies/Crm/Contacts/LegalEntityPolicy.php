<?php

namespace App\Policies\Crm\Contacts;

use App\Models\Crm\Contacts\LegalEntity;
use App\Models\System\User;
use Illuminate\Database\Eloquent\Builder;

class LegalEntityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [CRM] Contatos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LegalEntity $legalEntity)
    {
        if (!$user->hasPermissionTo('Visualizar [CRM] Contatos')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($legalEntity): Builder {
                    return $query->where('id', $legalEntity->contact->user_id);
                })
                ->exists();
        }

        if ($legalEntity->contact->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [CRM] Contatos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LegalEntity $legalEntity)
    {
        if (!$user->hasPermissionTo('Editar [CRM] Contatos')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($legalEntity): Builder {
                    return $query->where('id', $legalEntity->contact->user_id);
                })
                ->exists();
        }

        if ($legalEntity->contact->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LegalEntity $legalEntity)
    {
        if (!$user->hasPermissionTo('Deletar [CRM] Contatos')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            return $user->teams()
                ->whereHas('users', function (Builder $query) use ($legalEntity): Builder {
                    return $query->where('id', $legalEntity->contact->user_id);
                })
                ->exists();
        }

        if ($legalEntity->contact->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LegalEntity $legalEntity): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LegalEntity $legalEntity): bool
    {
        return false;
    }
}
