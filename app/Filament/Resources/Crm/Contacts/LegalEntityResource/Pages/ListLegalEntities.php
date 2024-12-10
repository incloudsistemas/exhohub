<?php

namespace App\Filament\Resources\Crm\Contacts\LegalEntityResource\Pages;

use App\Filament\Resources\Crm\Contacts\LegalEntityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegalEntities extends ListRecords
{
    protected static string $resource = LegalEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
