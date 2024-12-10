<?php

namespace App\Filament\Resources\Crm\Contacts\IndividualResource\Pages;

use App\Filament\Resources\Crm\Contacts\IndividualResource;
use App\Models\Crm\Contacts\Individual;
use App\Services\Crm\Contacts\IndividualService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIndividual extends EditRecord
{
    protected static string $resource = IndividualResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn(IndividualService $service, Actions\DeleteAction $action, Individual $record) =>
                    $service->preventIndividualDeleteIf(action: $action, individual: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $contact = $this->record->contact;

        $data['contact']['roles'] = $contact->roles->pluck('id')
            ->toArray();

        $data['contact']['name'] = $contact->name;
        $data['contact']['email'] = $contact->email;
        $data['contact']['additional_emails'] = $contact->additional_emails;
        $data['contact']['phones'] = $contact->phones;
        $data['contact']['source_id'] = $contact->source_id;
        $data['contact']['user_id'] = $contact->user_id;
        $data['contact']['status'] = $contact->status->value;
        $data['contact']['complement'] = $contact->complement;

        $data['legal_entities'] = $this->record->legalEntities->pluck('id')
            ->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->updateContact();
        $this->syncRoles();
        $this->syncLegalEntities();
    }

    protected function updateContact(): void
    {
        $this->data['contact']['additional_emails'] = array_values($this->data['contact']['additional_emails']);
        $this->data['contact']['phones'] = array_values($this->data['contact']['phones']);

        $this->record->contact->update($this->data['contact']);
    }

    protected function syncRoles(): void
    {
        $this->record->contact->roles()
            ->sync($this->data['contact']['roles']);
    }

    protected function syncLegalEntities(): void
    {
        $this->record->legalEntities()
            ->sync($this->data['legal_entities']);
    }
}
