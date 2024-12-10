<?php

namespace App\Services\System;

use App\Enums\ProfileInfos\UserStatusEnum;
use App\Models\System\CreciControlStage;
use App\Models\System\Team;
use App\Models\System\User;
use App\Models\System\UserCreciStage;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class UserService extends BaseService
{
    public function __construct(protected User $user)
    {
        //
    }

    public function tableSearchByNameAndCpf(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search): Builder {
            return $query->where('cpf', 'like', "%{$search}%")
                ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"])
                ->orWhere('name', 'like', "%{$search}%");
        });
    }

    public function tableSearchByPhone(Builder $query, string $search): Builder
    {
        return $query->whereRaw("JSON_EXTRACT(phones, '$[0].number') LIKE ?", ["%$search%"]);
    }

    public function tableSearchByCreciStage(Builder $query, string $search): Builder
    {
        $userCreciStagesTable = (new UserCreciStage())->getTable();
        $creciControlStagesTable = (new CreciControlStage())->getTable();

        return $query->whereHas('userCreciStages', function (Builder $subQuery) use ($search, $userCreciStagesTable, $creciControlStagesTable) {
            $subQuery->where('id', function ($subSubQuery) use ($userCreciStagesTable) {
                $subSubQuery->select(\DB::raw('max(id)'))
                    ->from($userCreciStagesTable)
                    ->whereColumn('user_creci_stages.user_id', 'users.id')
                    ->groupBy('user_creci_stages.user_id');
            })
                ->whereHas('creciControlStage', function (Builder $stageQuery) use ($search, $creciControlStagesTable) {
                    return $stageQuery->where("$creciControlStagesTable.name", 'like', '%' . $search . '%');
                });
        });
    }

    public function tableSearchByStatus(Builder $query, string $search): Builder
    {
        $statuses = UserStatusEnum::getAssociativeArray();

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
        $statuses = UserStatusEnum::getAssociativeArray();

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

    public function getUserOptionsBySearch(?string $search): array
    {
        return $this->user->byStatuses(statuses: [1])
            ->where(function (Builder $query) use ($search): Builder {
                return $query->where('cpf', 'like', "%{$search}%")
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"])
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->mapWithKeys(function ($item): array {
                $name = !empty($item->cpf) ? $item->name . ' - ' . $item->cpf : $item->name;
                return [$item->id => $name];
            })
            ->toArray();
    }

    public function getUserByRolesOptionsBySearch(?string $search, array $roles): array
    {
        return $this->user->byStatuses(statuses: [1])
            ->whereHas('roles', function (Builder $query) use ($roles): Builder {
                return $query->whereIn('id', $roles);
            })
            ->where(function (Builder $query) use ($search): Builder {
                return $query->where('cpf', 'like', "%{$search}%")
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"])
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->mapWithKeys(function ($item): array {
                $name = !empty($item->cpf) ? $item->name . ' - ' . $item->cpf : $item->name;
                return [$item->id => $name];
            })
            ->toArray();
    }

    // Single
    public function getUserOptionLabel(?int $value): ?string
    {
        return $this->user->find($value)?->name;
    }

    // Multiple
    public function getUserOptionsLabel(array $values): array
    {
        return $this->user->whereIn('id', $values)
            ->get()
            ->mapWithKeys(function ($item): array {
                $name = !empty($item->cpf) ? $item->name . ' - ' . $item->cpf : $item->name;
                return [$item->id => $name];
            })
            ->toArray();
    }

    public function getOptionsByTeamsByGroupedAgencies(): array
    {
        $teamsWithAgencies = Team::with('agency')
            ->byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('agency', function (Builder $query): Builder {
                return $query->where('status', 1); // 1 - Ativo
            })
            ->whereHas('users')
            ->get()
            ->groupBy('agency.name')
            ->map(function ($teams) {
                return $teams->pluck('name', 'id');
            })
            ->toArray();

        $teamsWithoutAgencies = Team::byStatuses(statuses: [1])
            ->whereDoesntHave('agency')
            ->whereHas('users')
            ->pluck('name', 'id')
            ->toArray();

        if (!empty($teamsWithoutAgencies)) {
            $teamsWithAgencies['Sem Agência'] = $teamsWithoutAgencies;
        }

        return $teamsWithAgencies;
    }

    public function tableFilterByTeams(Builder $query, array $data): Builder
    {
        if (!empty($data['values'])) {
            return $query->whereHas('teams', function (Builder $query) use ($data): Builder {
                return $query->whereIn('id', $data['values']); // 1 - active
            });
        }

        return $query;
    }

    public function getOptionsByCreciStages(): array
    {
        return CreciControlStage::whereHas('userCreciStages', function ($query) {
                $query->whereIn('id', function ($subQuery) {
                    $subQuery->select(\DB::raw('max(id)'))
                        ->from('user_creci_stages')
                        ->groupBy('user_id');
                });
            })
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByCreciStages(Builder $query, array $data): Builder
    {
        if (!empty($data['values'])) {
            return $query->whereHas('userCreciStages', function ($subQuery) use ($data) {
                $subQuery->where('id', function ($subSubQuery) {
                    $subSubQuery->select(\DB::raw('max(id)'))
                        ->from('user_creci_stages')
                        ->whereColumn('user_creci_stages.user_id', 'users.id')
                        ->groupBy('user_creci_stages.user_id');
                })
                ->whereIn('creci_control_stage_id', $data['values']);
            });
        }

        return $query;
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventUserDeleteIf($action, User $user): void
    {
        if (auth()->user()->id === $user->id) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de usuário'))
                ->warning()
                ->body(__('Você não pode excluir seu próprio usuário do sistema por questões de segurança.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
