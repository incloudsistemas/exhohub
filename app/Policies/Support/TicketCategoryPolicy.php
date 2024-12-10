<?php

namespace App\Policies\Support;

use App\Models\Support\TicketCategory;
use App\Models\System\User;
use Illuminate\Auth\Access\Response;

class TicketCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [Suporte] Categorias');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketCategory $ticketCategory): bool
    {
        return $user->hasPermissionTo(permission: 'Visualizar [Suporte] Categorias');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(permission: 'Cadastrar [Suporte] Categorias');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketCategory $ticketCategory): bool
    {
        return $user->hasPermissionTo(permission: 'Editar [Suporte] Categorias');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketCategory $ticketCategory): bool
    {
        return $user->hasPermissionTo(permission: 'Deletar [Suporte] Categorias');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TicketCategory $ticketCategory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TicketCategory $ticketCategory): bool
    {
        return false;
    }
}
