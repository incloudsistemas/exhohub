<?php

namespace App\Filament\Resources\Crm\Queues\QueueResource\Pages;

use App\Filament\Resources\Crm\Queues\QueueResource;
use App\Models\Crm\Queues\Queue;
use App\Services\Crm\Queues\QueueService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQueue extends EditRecord
{
    protected static string $resource = QueueResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn(QueueService $service, Actions\DeleteAction $action, Queue $record) =>
                    $service->preventQueueDeleteIf(action: $action, queue: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
