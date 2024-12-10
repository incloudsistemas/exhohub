<?php

namespace App\Filament\Resources\Support\TicketResource\Pages;

use App\Filament\Resources\Support\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->user()->id;

        $data['status'] = 0; // 0 - Aguardando atendimento.

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->attachApplicantUsers();
    }

    protected function attachApplicantUsers(): void
    {
        $this->record->users()
            ->attach($this->data['applicant_users'], ['role' => 2]); // 2 - Solicitante
    }
}
