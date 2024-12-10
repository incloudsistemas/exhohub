<?php

namespace App\Policies\Cms;

use App\Models\Cms\PostSlider;
use App\Models\System\User;

class MainPostSliderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (
            $user->hasPermissionTo(permission: 'Visualizar [CMS] Páginas') &&
            $user->hasPermissionTo(permission: 'Visualizar [CMS] Sliders')
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PostSlider $postSlider): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [CMS] Sliders');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [CMS] Sliders');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PostSlider $postSlider): bool
    {
        return $user->hasPermissionTo(permission: 'Editar [CMS] Sliders');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PostSlider $postSlider): bool
    {
        return $user->hasPermissionTo(permission: 'Deletar [CMS] Sliders');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PostSlider $postSlider): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PostSlider $postSlider): bool
    {
        return false;
    }
}
