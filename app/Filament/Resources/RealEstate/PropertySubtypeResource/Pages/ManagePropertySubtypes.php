<?php

namespace App\Filament\Resources\RealEstate\PropertySubtypeResource\Pages;

use App\Filament\Resources\RealEstate\PropertySubtypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePropertySubtypes extends ManageRecords
{
    protected static string $resource = PropertySubtypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
