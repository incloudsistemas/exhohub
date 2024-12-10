<?php

namespace App\Services\System;

use App\Models\System\Role;
use App\Models\System\User;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class RoleService extends BaseService
{
    public function __construct(protected Role $role)
    {
        //
    }

    public static function getArrayOfRolesToAvoidByAuthUserRoles(User $user): array
    {
        $userRoles = $user->roles->pluck('id')
            ->toArray();

        // avoid role 2 = client/customer, ALWAYS.
        // avoid role 1 = super-admin, if auth user role isn't super-admin.
        // avoid role 3 = admin, if auth user role isn't super-admin or admin.

        // 1 - Super-admin
        if (in_array(1, $userRoles)) {
            return [2];
        }

        // 3 - Admin
        if (in_array(3, $userRoles)) {
            return [1, 2];
        }

        // Other roles
        return [1, 2, 3];
    }

    public function getQueryByAuthUserRoles(Builder $query): Builder
    {
        $user = auth()->user();
        $rolesToAvoid = static::getArrayOfRolesToAvoidByAuthUserRoles(user: $user);

        return $query->whereNotIn('id', $rolesToAvoid)
            ->orderBy('id', 'asc');
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventRoleDeleteIf($action, Role $role): void
    {
        if ($role->users->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de nível de acesso'))
                ->warning()
                ->body(__('Este nível de acesso possui usuários associados. Para excluir, você deve primeiro desvincular todos os usuários que estão associados a ele.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
