<?php

namespace App\Filament\Resources\Crm\Funnels\FunnelResource\Pages;

use App\Filament\Resources\Crm\Funnels\FunnelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFunnels extends ListRecords
{
    protected static string $resource = FunnelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
