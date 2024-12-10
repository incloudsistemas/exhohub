<?php

namespace App\Filament\Resources\RealEstate\IndividualResource\Pages;

use App\Filament\Resources\RealEstate\IndividualResource;
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
        $this->createProperty();
        $this->createAddress();
        $this->attachCharacteristics();
        $this->attachContacts();
    }

    protected function createProperty(): void
    {
        $this->data['property']['embed_videos'] = array_values($this->data['property']['embed_videos']);

        if (auth()->user()->hasAnyRole(['Superadministrador', 'Administrador'])) {
            $this->data['property']['status'] = 1; // 1 - Ativo
        } else {
            $this->data['property']['status'] = 2; // 2 - Pendente
        }

        $this->record->property()
            ->create($this->data['property']);
    }

    protected function attachCharacteristics(): void
    {
        $mergedCharacteristics = array_merge(
            $this->data['property']['characteristics']['differences'],
            $this->data['property']['characteristics']['leisure'],
            $this->data['property']['characteristics']['security'],
            $this->data['property']['characteristics']['infrastructure'],
        );

        $this->record->property->characteristics()
            ->attach($mergedCharacteristics);
    }

    protected function createAddress(): void
    {
        $this->data['property']['address']['is_main'] = true;

        $this->record->property->address()
            ->create($this->data['property']['address']);
    }

    protected function attachContacts(): void
    {
        // Attach contact owners
        $contactOwners = collect($this->data['property']['contact_owners'])
            ->mapWithKeys(function ($id) {
                return [$id => ['role' => 1]]; // '1' - Owner / Proprietário(s)
            })
            ->all();

        $this->record->property->contacts()
            ->attach($contactOwners);
    }
}
