<?php

namespace App\Policies\RealEstate;

use App\Models\RealEstate\PropertySubtype;
use App\Models\System\User;

class PropertySubtypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Subtipos de Imóveis');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PropertySubtype $propertySubtype)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Subtipos de Imóveis');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [IMB] Subtipos de Imóveis');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PropertySubtype $propertySubtype)
    {
        return $user->hasPermissionTo(permission: 'Editar [IMB] Subtipos de Imóveis');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PropertySubtype $propertySubtype)
    {
        return $user->hasPermissionTo(permission: 'Deletar [IMB] Subtipos de Imóveis');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PropertySubtype $propertySubtype): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PropertySubtype $propertySubtype): bool
    {
        return false;
    }
}
