<?php

namespace App\Services\RealEstate;

use App\Enums\DefaultStatusEnum;
use App\Enums\RealEstate\PropertyTypeUsageEnum;
use App\Models\RealEstate\PropertySubtype;
use App\Models\RealEstate\PropertyType;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class PropertyTypeService extends BaseService
{
    public function __construct(protected PropertyType $propertyType)
    {
        //
    }

    public function tableSearchByUsage(Builder $query, string $search): Builder
    {
        $uses = PropertyTypeUsageEnum::getAssociativeArray();

        $matchingUses = [];
        foreach ($uses as $index => $usage) {
            if (stripos($usage, $search) !== false) {
                $matchingUses[] = $index;
            }
        }

        if ($matchingUses) {
            return $query->whereIn('usage', $matchingUses);
        }

        return $query;
    }

    public function tableSortByUsage(Builder $query, string $direction): Builder
    {
        $uses = PropertyTypeUsageEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($uses as $key => $usage) {
            $caseParts[] = "WHEN ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $usage;
        }

        $orderByCase = "CASE `usage` " . implode(' ', $caseParts) . " END";

        return $query->orderByRaw("$orderByCase $direction", $bindings);
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

    public function tableFilterByUsage(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        array_push($data['values'], '3'); // 3 - Residencial e Comercial

        return $query->whereIn('usage', $data['values']);
    }

    public function getOptionsBySubtypesWhereHasTypes(): array
    {
        // statuses 1 - active
        return PropertySubtype::byStatuses(statuses: [1])
            ->whereHas('types')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterBySubtypes(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('subtypes', function (Builder $query) use ($data): Builder {
            return $query->whereIn('id', $data['values']);
        });
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventPropertyTypeDeleteIf($action, PropertyType $propertyType): void
    {
        if ($propertyType->properties->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de tipo de imóvel'))
                ->warning()
                ->body(__('Este tipo possui imóveis associados. Para excluir, você deve primeiro desvincular todos os imóveis que estão associados a ele.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
