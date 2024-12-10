<?php

use App\Enums\RealEstate\RentWarrantiesEnum;
use App\Models\RealEstate\Property;

if (!function_exists('GetCanalProTransactionType')) {
    function GetCanalProTransactionType(Property $property): string
    {
        if ($property->propertable_type === 'real_estate_individuals') {
            return match ((int) $property->propertable->role->value) {
                1 => 'For Sale',
                2 => 'For Rent',
                3 => 'Sale/Rent',
            };
        }

        return 'For Sale';
    }
}

if (!function_exists('GetCanalProPropertyType')) {
    function GetCanalProPropertyType(Property $property): ?string
    {
        if (!isset($property->type->canal_pro_vrsync) || empty($property->type->canal_pro_vrsync)) {
            return null;
        }

        return ucfirst(strtolower($property->usage->name)) . " / {$property->type->canal_pro_vrsync}";
    }
}

if (!function_exists('GetCanalProWarranty')) {
    function GetCanalProWarranty(string $label): ?string
    {
        foreach (RentWarrantiesEnum::cases() as $warranty) {
            if ($warranty->getLabel() === $label) {
                return $warranty->name;
            }
        }

        return null;
    }
}

if (!function_exists('GetCanalProCharacteristics')) {
    function GetCanalProCharacteristics(Property $property): array
    {
        return $property->characteristics()
            ->where('canal_pro_vrsync', '<>', '')
            ->pluck('canal_pro_vrsync')
            ->toArray();
    }
}

if (!function_exists('GetCanalProDisplayAddress')) {
    function GetCanalProDisplayAddress(Property $property): string
    {
        // 1 - 'Completo', 2 - 'Somente bairro, cidade e uf', 3 - 'Somente rua, cidade e uf', 4 - 'Somente cidade e uf'.
        return match ((int) $property->show_address) {
            // Somente bairro, cidade e uf
            2, 4    => 'Neighborhood',
            // Somente rua, cidade e uf
            3       => 'Street',
            // Completo
            default => 'All',
        };
    }
}

if (!function_exists('GetCanalProConstructionStatus')) {
    function GetCanalProConstructionStatus(Property $property): ?string
    {
        if ($property->propertable_type === 'real_estate_enterprises') {
            return match ((int) $property->propertable->role) {
                1 => 'PLAN_ONLY',
                2 => 'UNDER_CONSTRUCTION',
                3 => 'BUILT',
            };
        }

        return null;
    }
}
