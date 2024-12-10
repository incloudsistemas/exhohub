<?php

namespace App\Services\RealEstate;

use App\Enums\RealEstate\EnterpriseRoleEnum;
use App\Models\Crm\Contacts\Contact;
use App\Models\RealEstate\Enterprise;
use App\Models\RealEstate\Property;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class EnterpriseService extends BaseService
{
    public function __construct(protected Property $property, protected Enterprise $enterprise)
    {
        //
    }

    public function tableSearchByRole(Builder $query, string $search): Builder
    {
        $roles = EnterpriseRoleEnum::getAssociativeArray();

        $matchingRoles = [];
        foreach ($roles as $index => $role) {
            if (stripos($role, $search) !== false) {
                $matchingRoles[] = $index;
            }
        }

        if ($matchingRoles) {
            return $query->whereIn('role', $matchingRoles);
        }

        return $query;
    }

    public function tableSortByRole(Builder $query, string $direction): Builder
    {
        $roles = EnterpriseRoleEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($roles as $key => $role) {
            $caseParts[] = "WHEN ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $role;
        }

        $orderByCase = "CASE role " . implode(' ', $caseParts) . " END";

        return $query->orderByRaw("$orderByCase $direction", $bindings);
    }

    public function tableSortByMinPrice(Builder $query, string $direction): Builder
    {
        return $query->orderBy('min_price', $direction);
    }

    public function tableFilterByPrice(Builder $query, array $data): Builder
    {
        $data['min_price'] = ConvertPtBrFloatStringToInt(value: $data['min_price']);
        $data['max_price'] = ConvertPtBrFloatStringToInt(value: $data['max_price']);

        return $query
            ->when(
                $data['min_price'],
                fn (Builder $query, $price): Builder =>
                $query->where('min_price', '>=', $price)
                    ->orWhere('max_price', '>=', $price),
            )
            ->when(
                $data['max_price'],
                fn (Builder $query, $price): Builder =>
                $query->where('min_price', '<=', $price)
                    ->orWhere('max_price', '<=', $price),
            );
    }

    public function tableFilterByRooms(Builder $query, array $data): Builder
    {
        return $query->when(
            $data['bedroom'],
            function (Builder $query, $bedroom): Builder {
                return $query->where(function (Builder $query) use ($bedroom) {
                    if ($bedroom == 4) {
                        $query->where('min_bedroom', '>=', $bedroom)
                            ->orWhere('max_bedroom', '>=', $bedroom);
                    } else {
                        $query->where('min_bedroom', '=', $bedroom)
                            ->orWhere('max_bedroom', '=', $bedroom);
                    }
                });
            }
        )->when(
            $data['suite'],
            function (Builder $query, $suite): Builder {
                return $query->where(function (Builder $query) use ($suite) {
                    if ($suite == 4) {
                        $query->where('min_suite', '>=', $suite)
                            ->orWhere('max_suite', '>=', $suite);
                    } else {
                        $query->where('min_suite', '=', $suite)
                            ->orWhere('max_suite', '=', $suite);
                    }
                });
            }
        )->when(
            $data['bathroom'],
            function (Builder $query, $bathroom): Builder {
                return $query->where(function (Builder $query) use ($bathroom) {
                    if ($bathroom == 4) {
                        $query->where('min_bathroom', '>=', $bathroom)
                            ->orWhere('max_bathroom', '>=', $bathroom);
                    } else {
                        $query->where('min_bathroom', '=', $bathroom)
                            ->orWhere('max_bathroom', '=', $bathroom);
                    }
                });
            }
        )->when(
            $data['garage'],
            function (Builder $query, $garage): Builder {
                return $query->where(function (Builder $query) use ($garage) {
                    if ($garage == 4) {
                        $query->where('min_garage', '>=', $garage)
                            ->orWhere('max_garage', '>=', $garage);
                    } else {
                        $query->where('min_garage', '=', $garage)
                            ->orWhere('max_garage', '=', $garage);
                    }
                });
            }
        );
    }

    public function tableFilterByUsefulArea(Builder $query, array $data): Builder
    {
        $data['min_useful_area'] = ConvertPtBrFloatStringToInt(value: $data['min_useful_area']);
        $data['max_useful_area'] = ConvertPtBrFloatStringToInt(value: $data['max_useful_area']);

        return $query
            ->when(
                $data['min_useful_area'],
                fn (Builder $query, $usefulArea): Builder =>
                $query->where('min_useful_area', '>=', $usefulArea)
                    ->orWhere('max_useful_area', '>=', $usefulArea),
            )
            ->when(
                $data['max_useful_area'],
                fn (Builder $query, $usefulArea): Builder =>
                $query->where('min_useful_area', '<=', $usefulArea)
                    ->orWhere('max_useful_area', '<=', $usefulArea),
            );
    }

    public function tableFilterByTotalArea(Builder $query, array $data): Builder
    {
        $data['min_total_area'] = ConvertPtBrFloatStringToInt(value: $data['min_total_area']);
        $data['max_total_area'] = ConvertPtBrFloatStringToInt(value: $data['max_total_area']);

        return $query
            ->when(
                $data['min_total_area'],
                fn (Builder $query, $totalArea): Builder =>
                $query->where('min_total_area', '>=', $totalArea)
                    ->orWhere('max_total_area', '>=', $totalArea),
            )
            ->when(
                $data['max_total_area'],
                fn (Builder $query, $totalArea): Builder =>
                $query->where('min_total_area', '<=', $totalArea)
                    ->orWhere('max_total_area', '<=', $totalArea),
            );
    }

    public function getOptionsByPropertyContactCompaniesRoleWhereHasProperties(int $role): array
    {
        // statuses 1 - active
        return Contact::byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('properties', function (Builder $query) use ($role): Builder {
                return $query->where('crm_contact_real_estate_property.role', $role);
            })
            ->get()
            ->mapWithKeys(function ($contact): array {
                return [$contact->id => $contact->name];
            })
            ->toArray();
    }

    public function tableFilterByPropertyContactCompaniesRole(Builder $query, int $role, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('property', function (Builder $query) use ($role, $data): Builder {
            return $query->whereHas('contacts', function (Builder $query) use ($role, $data): Builder {
                return $query->where('crm_contact_real_estate_property.role', $role)
                    ->whereIn('id', $data['values']);
            });
        });
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventEnterpriseDeleteIf($action, Enterprise $enterprise): void
    {
        //
    }
}
