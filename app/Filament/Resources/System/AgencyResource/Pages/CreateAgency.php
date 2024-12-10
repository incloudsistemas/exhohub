<?php

namespace App\Filament\Resources\System\AgencyResource\Pages;

use App\Filament\Resources\System\AgencyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAgency extends CreateRecord
{
    protected static string $resource = AgencyResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->attachPartners();
    }

    protected function attachPartners(): void
    {
        $data = collect($this->data['partners'])
            ->mapWithKeys(function ($id) {
                return [$id => ['role' => 1]]; // '1' - partners
            })
            ->all();

        // Attach partners
        $this->record->partners()
            ->attach($data);
    }
}
