<?php

namespace App\Filament\Resources\RealEstate\PropertyCharacteristicResource\Pages;

use App\Filament\Resources\RealEstate\PropertyCharacteristicResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePropertyCharacteristics extends ManageRecords
{
    protected static string $resource = PropertyCharacteristicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
