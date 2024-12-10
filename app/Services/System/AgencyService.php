<?php

namespace App\Services\System;

use App\Enums\DefaultStatusEnum;
use App\Models\System\Agency;
use App\Models\System\User;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class AgencyService extends BaseService
{
    public function __construct(protected Agency $agency)
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

    public function getQueryByActiveAgencies(Builder $query): Builder
    {
        return $query->byStatuses(statuses: [1]); // 1 - Ativo
    }

    public function getOptionsByPartnersWhereHasAgencies(): array
    {
        return User::byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('agencies', function (Builder $query): Builder {
                return $query->where('agency_user.role', 1); // 1 - partners
            })
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByPartners(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('partners', function (Builder $query) use ($data): Builder {
            return $query->whereIn('id', $data['values']);
        });
    }

    public function getOptionsByActiveAgencies(): array
    {
        return $this->agency->byStatuses([1])
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventAgencyDeleteIf($action, Agency $agency): void
    {
        if ($agency->users->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de agência'))
                ->warning()
                ->body(__('Esta agência possui usuários associados. Para excluir, você deve primeiro desvincular todos os usuários que estão associados a ele.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
