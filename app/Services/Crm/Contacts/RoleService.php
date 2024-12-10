<?php

namespace App\Services\Crm\Contacts;

use App\Enums\DefaultStatusEnum;
use App\Models\Crm\Contacts\Role;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class RoleService extends BaseService
{
    public function __construct(protected Role $role)
    {
        //
    }

    public function tableSearchByStatus(Builder $query, string $search): Builder
    {
        $statuses = DefaultStatusEnum::getAssociativeArray();

        $matchingStatuses = [];
        foreach ($statuses as $index => $status) {
            if (stripos($status, $search) !== false) {
                $matchingStatuses[] = $index;
            }
        }

        if ($matchingStatuses) {
            return $query->whereIn('status', $matchingStatuses);
        }

        return $query;
    }

    public function tableSortByStatus(Builder $query, string $direction): Builder
    {
        $statuses = DefaultStatusEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($statuses as $key => $status) {
            $caseParts[] = "WHEN ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $status;
        }

        $orderByCase = "CASE status " . implode(' ', $caseParts) . " END";

        return $query->orderByRaw("$orderByCase $direction", $bindings);
    }

    public function getOptionsByActiveContactRoles(): array
    {
        return $this->role->byStatuses(statuses: [1]) // 1 - Ativo
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getQueryByRolesWhereHasContacts(Builder $query): Builder
    {
        return $query->whereHas('contacts')
            ->orderBy('id', 'asc');
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventRoleDeleteIf($action, Role $role): void
    {
        if ($role->contacts->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de tipos de contatos'))
                ->warning()
                ->body(__('Este tipo possui contatos associados. Para excluir, você deve primeiro desvincular todos os contatos que estão associados a ele.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
