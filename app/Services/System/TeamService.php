<?php

namespace App\Services\System;

use App\Enums\DefaultStatusEnum;
use App\Models\System\Agency;
use App\Models\System\Team;
use App\Models\System\User;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class TeamService extends BaseService
{
    public function __construct(protected Team $team)
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

    public function getOptionsByAgenciesWhereHasTeams(): array
    {
        return Agency::byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('teams', function (Builder $query): Builder {
                return $query->where('status', 1); // 1 - Ativo
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByAgencies(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('agency', function (Builder $query) use ($data): Builder {
            return $query->whereIn('id', $data['values']);
        });
    }

    public function getOptionsByDirectorsWhereHasTeams(): array
    {
        return User::byStatuses(statuses: [1])
            ->whereHas('teams', function (Builder $query): Builder {
                return $query->where('team_user.role', 1) // 1 - Diretores
                    ->where('status', 1); // 1 - Ativo
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByDirectors(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('partners', function (Builder $query) use ($data): Builder {
            return $query->whereIn('id', $data['values']);
        });
    }

    public function getOptionsByGroupedAgencies(): array
    {
        $teamsWithAgencies = $this->team->with('agency')
            ->byStatuses(statuses: [1]) // 1 - active
            ->whereHas('agency', function (Builder $query): Builder {
                return $query->where('status', 1); // 1 - active
            })
            ->get()
            ->groupBy('agency.name')
            ->map(function ($teams) {
                return $teams->pluck('name', 'id');
            })
            ->toArray();

        $teamsWithoutAgencies = $this->team->byStatuses(statuses: [1])
            ->whereDoesntHave('agency')
            ->pluck('name', 'id')
            ->toArray();

        if (!empty($teamsWithoutAgencies)) {
            $teamsWithAgencies['Sem Agência'] = $teamsWithoutAgencies;
        }

        return $teamsWithAgencies;
    }

    public function getOptionsByAgency(?int $agency): array
    {
        return $this->team->byStatuses(statuses: [1]) // 1 - active
            ->when($agency, function (Builder $query, int $agency): Builder {
                return $query->where('agency_id', $agency);
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventTeamDeleteIf($action, Team $team): void
    {
        if ($team->users->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de time'))
                ->warning()
                ->body(__('Este time possui usuários associados. Para excluir, você deve primeiro desvincular todos os usuários que estão associados a ele.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
