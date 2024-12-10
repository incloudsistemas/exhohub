<?php

namespace App\Services\RealEstate;

use App\Enums\DefaultStatusEnum;
use App\Models\RealEstate\PropertySubtype;
use App\Models\RealEstate\PropertyType;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class PropertySubtypeService extends BaseService
{
    public function __construct(protected PropertySubtype $propertySubtype)
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

    public function getOptionsByTypesWhereHasSubtypes(): array
    {
        return PropertyType::byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('subtypes')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByTypes(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('types', function (Builder $query) use ($data): Builder {
            return $query->whereIn('id', $data['values']);
        });
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventPropertySubtypeDeleteIf($action, PropertySubtype $propertySubtype): void
    {
        if ($propertySubtype->properties->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de subtipo de imóvel'))
                ->warning()
                ->body(__('Este subtipo possui imóveis associados. Para excluir, você deve primeiro desvincular todos os imóveis que estão associados a ele.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
