<?php

namespace App\Filament\Resources\Crm\Contacts\IndividualResource\Pages;

use App\Filament\Resources\Crm\Contacts\IndividualResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIndividual extends CreateRecord
{
    protected static string $resource = IndividualResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->createContact();
        $this->attachRoles();
        $this->attachLegalEntities();
    }

    protected function createContact(): void
    {
        $this->data['contact']['additional_emails'] = array_values($this->data['contact']['additional_emails']);
        $this->data['contact']['phones'] = array_values($this->data['contact']['phones']);

        $this->record->contact()
            ->create($this->data['contact']);
    }

    protected function attachRoles(): void
    {
        $this->record->contact->roles()
            ->attach($this->data['contact']['roles']);
    }

    protected function attachLegalEntities(): void
    {
        $this->record->legalEntities()
            ->attach($this->data['legal_entities']);
    }
}
