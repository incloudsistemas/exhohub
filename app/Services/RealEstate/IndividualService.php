<?php

namespace App\Services\RealEstate;

use App\Enums\RealEstate\IndividualRoleEnum;
use App\Models\Crm\Contacts\Contact;
use App\Models\RealEstate\Individual;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyType;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class IndividualService extends BaseService
{
    public function __construct(protected Property $property, protected Individual $individual)
    {
        //
    }

    public function getPropertyTitle(?int $typeId, ?string $bedroom, ?string $city, ?string $uf, ?string $district, ?int $role): string
    {
        $title = '';

        if ($typeId) {
            $type = PropertyType::findOrFail($typeId);
            $title .= "{$type->name}";
        }

        if ($bedroom) {
            $title .= (int) $bedroom > 1 ? " {$bedroom} quartos" : " {$bedroom} quarto";
        }

        $roleTitles = [
            1 => 'à venda',
            2 => 'para alugar',
            3 => 'à venda e aluguel'
        ];

        if ($role && array_key_exists($role, $roleTitles)) {
            $title .= " {$roleTitles[$role]}";
        }

        if ($district) {
            $title .= " em {$district}";
        }

        if ($city && $uf) {
            $title .= $district ? ", {$city} {$uf}" : " em {$city} {$uf}";
        }

        return trim($title);
    }

    public function tableSearchByRole(Builder $query, string $search): Builder
    {
        $roles = IndividualRoleEnum::getAssociativeArray();

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
        $roles = IndividualRoleEnum::getAssociativeArray();

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

    public function tableSortBySalePrice(Builder $query, string $direction): Builder
    {
        return $query->orderBy('sale_price', $direction);
    }

    public function tableSortByRentPrice(Builder $query, string $direction): Builder
    {
        return $query->orderBy('rent_price', $direction);
    }

    public function tableFilterByRoles(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        array_push($data['values'], 3); // 3 - Venda e Aluguel

        return $query->whereIn('role', $data['values']);
    }

    public function tableFilterBySalePrice(Builder $query, array $data): Builder
    {
        $data['min_sale_price'] = ConvertPtBrFloatStringToInt(value: $data['min_sale_price']);
        $data['max_sale_price'] = ConvertPtBrFloatStringToInt(value: $data['max_sale_price']);

        return $query
            ->when(
                $data['min_sale_price'],
                fn (Builder $query, $price): Builder =>
                $query->where('sale_price', '>=', $price),
            )
            ->when(
                $data['max_sale_price'],
                fn (Builder $query, $price): Builder =>
                $query->where('sale_price', '<=', $price),
            );
    }

    public function tableFilterByRentPrice(Builder $query, array $data): Builder
    {
        $data['min_rent_price'] = ConvertPtBrFloatStringToInt(value: $data['min_rent_price']);
        $data['max_rent_price'] = ConvertPtBrFloatStringToInt(value: $data['max_rent_price']);

        return $query
            ->when(
                $data['min_rent_price'],
                fn (Builder $query, $price): Builder =>
                $query->where('rent_price', '>=', $price),
            )
            ->when(
                $data['max_rent_price'],
                fn (Builder $query, $price): Builder =>
                $query->where('rent_price', '<=', $price),
            );
    }

    public function tableFilterByRooms(Builder $query, array $data): Builder
    {
        return $query->when(
            $data['bedroom'],
            function (Builder $query, $bedroom): Builder {
                return $query->where(function (Builder $query) use ($bedroom) {
                    if ($bedroom == 4) {
                        $query->where('bedroom', '>=', $bedroom);
                    } else {
                        $query->where('bedroom', '=', $bedroom);
                    }
                });
            }
        )->when(
            $data['suite'],
            function (Builder $query, $suite): Builder {
                return $query->where(function (Builder $query) use ($suite) {
                    if ($suite == 4) {
                        $query->where('suite', '>=', $suite);
                    } else {
                        $query->where('suite', '=', $suite);
                    }
                });
            }
        )->when(
            $data['bathroom'],
            function (Builder $query, $bathroom): Builder {
                return $query->where(function (Builder $query) use ($bathroom) {
                    if ($bathroom == 4) {
                        $query->where('bathroom', '>=', $bathroom);
                    } else {
                        $query->where('bathroom', '=', $bathroom);
                    }
                });
            }
        )->when(
            $data['garage'],
            function (Builder $query, $garage): Builder {
                return $query->where(function (Builder $query) use ($garage) {
                    if ($garage == 4) {
                        $query->where('garage', '>=', $garage);
                    } else {
                        $query->where('garage', '=', $garage);
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
                $query->where('useful_area', '>=', $usefulArea),
            )
            ->when(
                $data['max_useful_area'],
                fn (Builder $query, $usefulArea): Builder =>
                $query->where('useful_area', '<=', $usefulArea),
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
                $query->where('total_area', '>=', $totalArea),
            )
            ->when(
                $data['max_total_area'],
                fn (Builder $query, $totalArea): Builder =>
                $query->where('total_area', '<=', $totalArea),
            );
    }

    public function getOptionsByPropertyContactOwnersWhereHasProperties(): array
    {
        // statuses 1 - active
        return Contact::byStatuses(statuses: [1])
            ->whereHas('properties', function (Builder $query): Builder {
                return $query->where('crm_contact_real_estate_property.role', 1); // 1 - Proprietário(s)
            })
            ->get()
            ->mapWithKeys(function ($contact): array {
                return [$contact->id => $contact->name];
            })
            ->toArray();
    }

    public function tableFilterByPropertyContactOwners(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('property', function (Builder $query) use ($data): Builder {
            return $query->whereHas('contacts', function (Builder $query) use ($data): Builder {
                return $query->where('crm_contact_real_estate_property.role', 1) // 1 - Proprietário(s);
                    ->whereIn('id', $data['values']);
            });
        });
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventIndividualDeleteIf($action, Individual $individual): void
    {
        //
    }
}
