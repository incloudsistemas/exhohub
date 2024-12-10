<?php

namespace App\Filament\Resources\System\TeamResource\Pages;

use App\Filament\Resources\System\TeamResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->attachDirectors();
        $this->attachManagers();
        $this->attachRealtors();
    }

    protected function attachDirectors(): void
    {
        $data = collect($this->data['directors'])
            ->mapWithKeys(function ($id) {
                return [$id => ['role' => 1]]; // '1' - directors
            })
            ->all();

        // Attach directors
        $this->record->directors()
            ->attach($data);
    }

    protected function attachManagers(): void
    {
        $data = collect($this->data['managers'])
            ->mapWithKeys(function ($id) {
                return [$id => ['role' => 2]]; // '2' - managers
            })
            ->all();

        // Attach managers
        $this->record->managers()
            ->attach($data);
    }

    protected function attachRealtors(): void
    {
        $data = collect($this->data['realtors'])
            ->mapWithKeys(function ($id) {
                return [$id => ['role' => 3]]; // '3' - realtors
            })
            ->all();

        // Attach realtors
        $this->record->realtors()
            ->attach($data);
    }
}
