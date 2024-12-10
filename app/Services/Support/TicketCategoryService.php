<?php

namespace App\Services\Support;

use App\Enums\DefaultStatusEnum;
use App\Models\Support\TicketCategory;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class TicketCategoryService extends BaseService
{
    public function __construct(protected TicketCategory $ticketCategory)
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

    public function getQueryByTicketCategories(Builder $query): Builder
    {
        return $query->byStatuses(statuses: [1]); // 1 - Ativo
    }

    public function getOptionsByTicketCategories(): array
    {
        return $this->agency->byStatuses([1]) // 1 - Ativo
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getQueryByTicketCategoriesDepartment(Builder $query, ?int $department): Builder
    {
        return $query->byStatuses(statuses: [1]) // 1 - Ativo
            ->when($department, function ($query) use ($department): Builder {
                return $query->whereHas('departments', function (Builder $query) use ($department): Builder {
                    return $query->where('id', $department);
                });
            });
    }

    public function getOptionsByTicketCategoriesDepartment(?int $department): array
    {
        return $this->agency->byStatuses([1]) // 1 - Ativo
            ->when($department, function ($query) use ($department): Builder {
                return $query->whereHas('departments', function (Builder $query) use ($department): Builder {
                    return $query->where('id', $department);
                });
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventTicketCategoryDeleteIf($action, TicketCategory $ticketCategory): void
    {
        //
    }
}
