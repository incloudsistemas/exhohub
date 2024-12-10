<?php

namespace App\Filament\Resources\RealEstate\PropertyTypeResource\Pages;

use App\Filament\Resources\RealEstate\PropertyTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePropertyTypes extends ManageRecords
{
    protected static string $resource = PropertyTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
