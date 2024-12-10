<?php

namespace App\Filament\Resources\Support\TicketResource\Pages;

use App\Filament\Resources\Support\TicketResource;
use App\Models\Support\Ticket;
use App\Services\Support\TicketService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected ?int $currentStatus = null;

    protected ?bool $hasComments = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn(TicketService $service, Actions\DeleteAction $action, Ticket $record) =>
                    $service->preventTicketDeleteIf(action: $action, ticket: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        $this->currentStatus = $this->record->status->value;

        $this->hasComments = $this->record->comments()->exists();
    }

    protected function afterSave(): void
    {
        $this->addResponsible();
    }

    protected function addResponsible(): void
    {
        if (!$this->hasComments) {
            $newComments = $this->data['comments'] ?? [];

            if (!empty($newComments)) {
                $firstComment = reset($newComments) ?: null;

                if ($firstComment && isset($firstComment['user_id'])) {
                    $this->record->users()
                        ->attach($firstComment['user_id'], ['role' => 1]); // 1 - Responsável
                }
            }
        }
    }
}
