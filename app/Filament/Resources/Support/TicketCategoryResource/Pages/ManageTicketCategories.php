<?php

namespace App\Filament\Resources\Support\TicketCategoryResource\Pages;

use App\Filament\Resources\Support\TicketCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTicketCategories extends ManageRecords
{
    protected static string $resource = TicketCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
