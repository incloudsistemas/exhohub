<?php

namespace App\Filament\Resources\Crm\Queues\QueueResource\Pages;

use App\Filament\Resources\Crm\Queues\QueueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQueues extends ListRecords
{
    protected static string $resource = QueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
