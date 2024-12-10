<?php

namespace App\Filament\Resources\System\AgencyResource\Pages;

use App\Filament\Resources\System\AgencyResource;
use App\Models\System\Agency;
use App\Services\System\AgencyService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgency extends EditRecord
{
    protected static string $resource = AgencyResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn (AgencyService $service, Actions\DeleteAction $action, Agency $record) =>
                    $service->preventAgencyDeleteIf(action: $action, agency: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['partners'] = $this->record->partners()
            ->pluck('id')
            ->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncPartners();
    }

    private function syncPartners(): void
    {
        $data = collect($this->data['partners'])
            ->mapWithKeys(function ($id) {
                return [$id => ['role' => 1]]; // '1' - partners
            })
            ->all();

        // Attach partners
        $this->record->partners()
            ->wherePivot('role', 1)
            ->sync($data);
    }
}
