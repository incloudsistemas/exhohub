<?php

namespace App\Policies\RealEstate;

use App\Models\RealEstate\PropertyType;
use App\Models\System\User;

class PropertyTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Tipos de Imóveis');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PropertyType $propertyType)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Tipos de Imóveis');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [IMB] Tipos de Imóveis');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PropertyType $propertyType)
    {
        return $user->hasPermissionTo(permission: 'Editar [IMB] Tipos de Imóveis');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PropertyType $propertyType)
    {
        return $user->hasPermissionTo(permission: 'Deletar [IMB] Tipos de Imóveis');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PropertyType $propertyType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PropertyType $propertyType): bool
    {
        return false;
    }
}
