<?php

namespace App\Services\RealEstate;

use App\Enums\RealEstate\PropertyStatusEnum;
use App\Enums\RealEstate\PropertyUsageEnum;
use App\Models\Polymorphics\Address;
use App\Models\RealEstate\Individual;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyCharacteristic;
use App\Models\RealEstate\PropertySubtype;
use App\Models\RealEstate\PropertyType;
use App\Models\System\User;
use App\Services\BaseService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class PropertyService extends BaseService
{
    protected string $propertyTable;

    public function __construct(protected Property $property)
    {
        $this->propertyTable = $property->getTable();
    }

    public function getOptionsByActivePropertyTypesUsage(?int $usage): array
    {
        $arr = [$usage, 3]; // 3 - Residencial e Comercial

        return PropertyType::byStatuses(statuses: [1]) // 1 - Ativo
            ->whereIn('usage', $arr)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getPropertyCode(int $typeId): string
    {
        $type = PropertyType::findOrFail($typeId);

        $countPropertyByType = $this->property->where('type_id', $typeId)
            ->count();

        do {
            $num = str_pad(++$countPropertyByType, 4, '0', STR_PAD_LEFT);
            $generatedCode = $type->abbr . '_' . $num;

            $existingCode = $this->property->where('code', $generatedCode)
                ->exists();
        } while ($existingCode);

        return $generatedCode;
    }

    public function getOptionsByActivePropertySubtypesType(?int $typeId): array
    {
        return PropertySubtype::byStatuses(statuses: [1]) // 1 - active
            ->whereHas('types', function (Builder $query) use ($typeId): Builder {
                return $query->where('id', $typeId);
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getOptionsByActiveCharacteristicsRoles(array $roles): array
    {
        return PropertyCharacteristic::byStatuses(statuses: [1]) // 1 - active
            ->byRoles(roles: $roles)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function validateCodeRule(?Model $record, string $attribute, string $state, Closure $fail): void
    {
        $exists = $this->property->where('code', $state)
            ->when($record, function ($query) use ($record): Builder {
                $propertableType = MorphMapByClass(model: Model::class);
                return $query->where('propertable_type', $propertableType)
                    ->where('propertable_id', '<>', $record->id);
            })
            ->first();

        if ($exists) {
            $fail(__('O valor informado para o campo código já está em uso.', ['attribute' => $attribute]));
        }
    }

    public function validateSlugRule(?Model $record, string $attribute, string $state, Closure $fail): void
    {
        $exists = $this->property->where('slug', $state)
            ->when($record, function ($query) use ($record): Builder {
                $propertableType = MorphMapByClass(model: Model::class);
                return $query->where('propertable_type', $propertableType)
                    ->where('propertable_id', '<>', $record->id);
            })
            ->first();

        if ($exists) {
            $fail(__('O valor informado para o campo slug já está em uso.', ['attribute' => $attribute]));
        }
    }

    public function getTableDisplayProperty(Property $property): string
    {
        $title = LimitCharsFromString($property->title, 80);
        $display = "<span class='font-semibold' title='{$property->title}'>{$title}</span>";

        $display .= "<p class='text-sm text-gray-500 dark:text-gray-400'>
            {$property->address->display_district_city_uf}
        </p>";

        $individualRelation = MorphMapByClass(model: Individual::class);

        if ($property->propertable_type === $individualRelation) {
            $details = [];

            foreach (
                [
                    'bedroom'  => 'quarto(s)',
                    'bathroom' => 'banheiro(s)',
                    'garage'   => 'vaga(s)'
                ] as $type => $label
            ) {
                $count = trim($property->propertable->$type);

                if (!empty($count) && $count > 0) {
                    $details[] = "{$count} <span class='text-xs'>{$label}</span>";
                }
            }

            if (!empty($details)) {
                $display .= "<p class='text-sm text-gray-500 dark:text-gray-400'>" . implode(' | ', $details) . "</p>";
            }

            $prices = [];
            foreach (
                [
                    'sale_price' => 'Venda: R$ ',
                    'rent_price' => 'Aluguel: R$ '
                ] as $type => $label
            ) {
                $price = trim($property->propertable->$type);

                if (!empty($price)) {
                    $rentPeriod = $type === 'rent_price' ? "<span class='text-xs'>/{$property->propertable->display_rent_period}</span>" : "";
                    $prices[] = "<span class='text-xs'>{$label}</span>" . $property->propertable->{"display_$type"} . $rentPeriod;
                }
            }

            if (!empty($prices)) {
                $display .= "<p class='mt-2 text-sm text-gray-500 dark:text-gray-400'>" . implode(' | ', $prices) . "</p>";
            }
        } else {
            if ($property->propertable->display_bedroom && $property->propertable->max_bedroom > 0) {
                $components[] = trim($property->propertable->display_bedroom) . " <span class='text-xs'>quarto(s)</span>";
            }

            if ($property->propertable->display_bathroom && $property->propertable->max_bathroom > 0) {
                $components[] = trim($property->propertable->display_bathroom) . " <span class='text-xs'>banheiro(s)</span>";
            }

            if ($property->propertable->display_garage && $property->propertable->max_garage > 0) {
                $components[] = trim($property->propertable->display_garage) . " <span class='text-xs'>vaga(s)</span>";
            }

            if (isset($components)) {
                $components = implode(' | ', $components);
                $display .= "<p class='text-sm text-gray-500 dark:text-gray-400'>{$components}</p>";
            }

            $display .= "<p class='mt-2 text-sm text-gray-500 dark:text-gray-400'>
                R$ {$property->propertable->display_price}
            </p>";
        }

        if (!empty(trim($property->condo_price))) {
            $taxComponents[] = "<span class='text-xs'>Cond.: R$ </span> {$property->display_condo_price}";
        }

        if (!empty(trim($property->tax_price))) {
            $taxComponents[] = "<span class='text-xs'>IPTU: R$ </span> {$property->display_tax_price}";
        }

        if (isset($taxComponents)) {
            $taxComponents = implode(' | ', $taxComponents);
            $display .= "<p class='text-sm text-gray-500 dark:text-gray-400'>{$taxComponents}</p>";
        }

        return $display;
    }

    public function tableSearchByPropertyTitleCodeAndUsage(Builder $query, string $search): Builder
    {
        $usages = PropertyUsageEnum::getAssociativeArray();

        $matchingUsages = [];
        foreach ($usages as $index => $usage) {
            if (stripos($usage, $search) !== false) {
                $matchingUsages[] = $index;
            }
        }

        return $query->whereHas('property', function (Builder $query) use ($search, $matchingUsages): Builder {
            return $query->where('title', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereIn('usage', $matchingUsages);
        });
    }

    public function tableSearchByPropertyTypeAndSubtype(Builder $query, string $search): Builder
    {
        return $query->whereHas('property', function (Builder $query) use ($search): Builder {
            return $query->whereHas('type', function (Builder $query) use ($search): Builder {
                return $query->where('name', 'like', "%{$search}%");
            })
                ->orWhereHas('subtype', function (Builder $query) use ($search): Builder {
                    return $query->where('name', 'like', "%{$search}%");
                });
        });
    }

    public function tableSearchByPropertyAddress(Builder $query, string $search): Builder
    {
        return $query->whereHas('property', function (Builder $query) use ($search): Builder {
            return $query->whereHas('address', function (Builder $query) use ($search): Builder {
                return $query->where('district', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('uf', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        });
    }

    public function tableSortByPropertyCondoPrice(Builder $query, string $direction): Builder
    {
        return $query->whereHas('property', function (Builder $query) use ($direction): Builder {
            return $query->orderBy('condo_price', $direction);
        });
    }

    public function tableSearchByPropertyStatus(Builder $query, string $search): Builder
    {
        $statuses = PropertyStatusEnum::getAssociativeArray();

        $matchingStatuses = [];
        foreach ($statuses as $index => $status) {
            if (stripos($status, $search) !== false) {
                $matchingStatuses[] = $index;
            }
        }

        if ($matchingStatuses) {
            return $query->whereHas('property', function (Builder $query) use ($matchingStatuses): Builder {
                return $query->whereIn('status', $matchingStatuses);
            });
        }

        return $query;
    }

    public function tableSortByPropertyStatus(Builder $query, string $direction): Builder
    {
        $propertableType = MorphMapByClass(model: $query->getModel()::class);
        $statuses = PropertyStatusEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($statuses as $key => $status) {
            $caseParts[] = "WHEN (SELECT status FROM {$this->propertyTable} WHERE {$this->propertyTable}.propertable_type = '{$propertableType}' AND {$this->propertyTable}.propertable_id = {$propertableType}.id) = ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $status;
        }

        $orderByCase = "CASE " . implode(' ', $caseParts) . " END";

        return $query->selectRaw("*, ({$orderByCase}) as display_status", $bindings)
            ->orderBy('display_status', $direction);
    }

    public function getOptionsByPropertyTypesWhereHasProperties(string $propertableType): array
    {
        return PropertyType::byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('properties', function (Builder $query) use ($propertableType): Builder {
                return $query->where('propertable_type', $propertableType);
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByPropertyTypes(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('property', function (Builder $query) use ($data): Builder {
            return $query->whereIn('type_id', $data['values']);
        });
    }

    public function getOptionsByPropertyDistrictsWhereHasProperties(string $propertableType): array
    {
        $addressableType = MorphMapByClass(model: get_class($this->property));

        $district = Address::where('addressable_type', $addressableType)
            ->whereHasMorph('addressable', get_class($this->property), function ($query) use ($propertableType): Builder {
                return $query->where('propertable_type', $propertableType);
            })
            ->distinct('district')
            ->pluck('district', 'district')
            ->toArray();

        $district = array_filter($district, function ($value) {
            return !empty($value);
        });

        return $district;
    }

    public function tableFilterByPropertyDistricts(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('property', function (Builder $query) use ($data): Builder {
            return $query->whereHas('address', function (Builder $query) use ($data): Builder {
                return $query->whereIn('district', $data['values']);
            });
        });
    }

    public function tableFilterByPropertyCondoPrice(Builder $query, array $data): Builder
    {
        $data['min_condo_price'] = ConvertPtBrFloatStringToInt(value: $data['min_condo_price']);
        $data['max_condo_price'] = ConvertPtBrFloatStringToInt(value: $data['max_condo_price']);

        return $query
            ->when(
                $data['min_condo_price'],
                fn(Builder $query, $price): Builder =>
                $query->whereHas('property', function (Builder $query) use ($price): Builder {
                    return $query->where('condo_price', '>=', $price);
                }),
            )
            ->when(
                $data['max_condo_price'],
                fn(Builder $query, $price): Builder =>
                $query->whereHas('property', function (Builder $query) use ($price): Builder {
                    return $query->where('condo_price', '<=', $price);
                }),
            );
    }

    public function getOptionsByPropertyOwnersWhereHasProperties(string $propertableType): array
    {
        return User::whereHas('properties', function (Builder $query) use ($propertableType): Builder {
            return $query->where('propertable_type', $propertableType);
        })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByPropertyOwners(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('property', function (Builder $query) use ($data): Builder {
            return $query->whereIn('user_id', $data['values']);
        });
    }

    public function tableFilterByPropertyStatuses(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('property', function (Builder $query) use ($data): Builder {
            return $query->whereIn('status', $data['values']);
        });
    }

    public function tableFilterByPropertyCreatedAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['created_from'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('property', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('created_at', '>=', $date);
                }),
            )
            ->when(
                $data['created_until'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('property', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('created_at', '<=', $date);
                }),
            );
    }

    public function tableFilterByPropertyUpdatedAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['updated_from'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('property', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('updated_at', '>=', $date);
                }),
            )
            ->when(
                $data['updated_until'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('property', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('updated_at', '<=', $date);
                }),
            );
    }

    public function downloadImages(Property $property): BinaryFileResponse|JsonResponse
    {
        $zip = new ZipArchive();

        $zipFileName = "{$property->slug}.zip";

        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $addZipFile = function (string $img, int $key = 0) use ($zip) {
                $filePath = GetUrlPath($img);
                $fileName = sprintf('%02d', $key) . '-' . basename($filePath);
                return $zip->addFile($filePath, $fileName);
            };

            // Rest of the images
            foreach ($property->propertable->getMedia('images') as $key => $mediaItem) {
                $img = CreateThumb(
                    src: $mediaItem->getUrl(),
                    width: 1280,
                    height: 800,
                    watermark: $property->show_watermark,
                    watermarkPosition: $property->display_watermark_position
                );

                $addZipFile(img: $img, key: $key + 1);
            }

            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } else {
            return response()->json(['error' => 'Não foi possível criar o arquivo zip.'], 404);
        }
    }
}
