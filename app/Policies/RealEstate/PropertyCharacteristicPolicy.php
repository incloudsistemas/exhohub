<?php

namespace App\Policies\RealEstate;

use App\Models\RealEstate\PropertyCharacteristic;
use App\Models\System\User;

class PropertyCharacteristicPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Características dos Imóveis');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PropertyCharacteristic $propertyCharacteristic)
    {
        return $user->hasPermissionTo(permission: 'Visualizar [IMB] Características dos Imóveis');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [IMB] Características dos Imóveis');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PropertyCharacteristic $propertyCharacteristic)
    {
        return $user->hasPermissionTo(permission: 'Editar [IMB] Características dos Imóveis');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PropertyCharacteristic $propertyCharacteristic)
    {
        return $user->hasPermissionTo(permission: 'Deletar [IMB] Características dos Imóveis');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PropertyCharacteristic $propertyCharacteristic): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PropertyCharacteristic $propertyCharacteristic): bool
    {
        return false;
    }
}
