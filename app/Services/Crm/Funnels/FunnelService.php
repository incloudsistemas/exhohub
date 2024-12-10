<?php

namespace App\Services\Crm\Funnels;

use App\Enums\DefaultStatusEnum;
use App\Models\Crm\Funnels\Funnel;
use App\Models\Crm\Funnels\FunnelStage;
use App\Models\Crm\Funnels\FunnelSubstage;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class FunnelService extends BaseService
{
    public function __construct(protected Funnel $funnel)
    {
        //
    }

    public function getQueryByStagesIgnoringClosure(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereNotIn('business_probability', [100, 0])
                ->orWhereNull('business_probability');
        });
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

    public function getQueryByFunnels(Builder $query): Builder
    {
        return $query->byStatuses([1]) // 1 - Ativo
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc');
    }

    public function getOptionsByFunnels(): array
    {
        return $this->funnel->byStatuses([1]) // 1 - Ativo
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getQueryByFunnelStagesFunnel(Builder $query, ?int $funnelId): Builder
    {
        return $query->where('funnel_id', $funnelId)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc');
    }

    public function getOptionsByFunnelStagesFunnel(?int $funnelId): array
    {
        return FunnelStage::where('funnel_id', $funnelId)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getQueryByFunnelSubstagesFunnelStage(Builder $query, ?int $funnelStageId): Builder
    {
        return $query->where('funnel_stage_id', $funnelStageId)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc');
    }

    public function getOptionsByFunnelSubstagesFunnelStage(?int $funnelStageId): array
    {
        return FunnelSubstage::where('funnel_stage_id', $funnelStageId)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getFunnelStageData(?int $funnelStageId): ?FunnelStage
    {
        if (!$funnelStageId) {
            return null;
        }

        return FunnelStage::find($funnelStageId);
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventFunnelDeleteIf($action, Funnel $funnel): void
    {
        //
    }
}
