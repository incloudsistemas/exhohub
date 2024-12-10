<?php

namespace App\Filament\Resources\Crm\Queues\QueueResource\Pages;

use App\Filament\Resources\Crm\Queues\QueueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQueue extends CreateRecord
{
    protected static string $resource = QueueResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
}
