<?php

namespace App\Services\Crm\Business;

use App\Enums\Crm\Business\PriorityEnum;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Funnels\Funnel;
use App\Models\System\User;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class BusinessService extends BaseService
{
    public function __construct(protected Business $business)
    {
        //
    }

    public function getCommissionPrice(?string $price, ?string $commissionPercentage): array
    {
        $result = [
            'price' => '',
        ];

        if ($price) {

            $price = ConvertPtBrFloatStringToInt(value: $price);

            $commissionPercentage = !empty($commissionPercentage)
                ? ConvertPtBrFloatStringToInt(value: $commissionPercentage)
                : 0;

            $commissionPercentage = round(floatval($commissionPercentage) / 100, precision: 2);

            $price = ($price * $commissionPercentage) / 100;
            $price = round(floatval($price) / 100, precision: 2);
            $result['price'] = number_format($price, 2, ',', '.');
        }

        return $result;
    }

    public function tableSearchByPriority(Builder $query, string $search): Builder
    {
        $priorities = PriorityEnum::getAssociativeArray();

        $matchingStatuses = [];
        foreach ($priorities as $index => $priority) {
            if (stripos($priority, $search) !== false) {
                $matchingStatuses[] = $index;
            }
        }

        if ($matchingStatuses) {
            return $query->whereIn('priority', $matchingStatuses);
        }

        return $query;
    }

    public function tableSortByPriority(Builder $query, string $direction): Builder
    {
        $priorities = PriorityEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($priorities as $key => $priority) {
            $caseParts[] = "WHEN ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $priority;
        }

        $orderByCase = "CASE priority " . implode(' ', $caseParts) . " END";

        return $query->orderByRaw("$orderByCase $direction", $bindings);
    }

    public function getOptionsByFunnelsWhereHasBusiness(): array
    {
        return Funnel::whereHas('businessFunnelStages')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getBusinessDefaultFunnel(): Funnel
    {
        return Funnel::byStatuses([1]) // 1 - Ativo
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->firstOrFail();
    }

    public function tableFilterByFunnelStageAndSubstages(Builder $query, array $data): Builder
    {
        return $query
            // ->when(
            //     $data['funnel'],
            //     fn(Builder $query, $funnel): Builder =>
            //     $query->whereHas('businessFunnelStages', function (Builder $query) use ($funnel): Builder {
            //         return $query->where('funnel_id', $funnel)
            //             ->orderBy('business_at', 'desc')
            //             ->orderBy('created_at', 'desc')
            //             ->limit(1);
            //     }),
            // )
            ->when(
                $data['stage'],
                fn(Builder $query, $stage): Builder =>
                $query->whereHas('businessFunnelStages', function (Builder $query) use ($stage): Builder {
                    return $query->where('funnel_stage_id', $stage)
                        ->orderBy('business_at', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->limit(1);
                }),
            )
            ->when(
                $data['substages'],
                fn(Builder $query, $substages): Builder =>
                $query->whereHas('businessFunnelStages', function (Builder $query) use ($substages): Builder {
                    return $query->whereIn('funnel_substage_id', $substages)
                        ->orderBy('business_at', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->limit(1);
                }),
            );
    }

    public function tableFilterGetOptionsByOwners(): array
    {
        return User::byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('business')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterGetQueryByOwners(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('owner', function (Builder $query) use ($data): Builder {
            return $query->whereIn('id', $data['values']);
        });
    }

    public function tableFilterByBusinessAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['business_from'],
                fn(Builder $query, $date): Builder =>
                $query->whereDate('business_at', '>=', $date),
            )
            ->when(
                $data['business_until'],
                fn(Builder $query, $date): Builder =>
                $query->whereDate('business_at', '<=', $date),
            );
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventBusinessDeleteIf($action, Business $business): void
    {
        //
    }
}
