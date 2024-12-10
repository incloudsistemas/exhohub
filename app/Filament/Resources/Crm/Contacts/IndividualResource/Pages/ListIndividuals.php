<?php

namespace App\Filament\Resources\Crm\Contacts\IndividualResource\Pages;

use App\Filament\Resources\Crm\Contacts\IndividualResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIndividuals extends ListRecords
{
    protected static string $resource = IndividualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
