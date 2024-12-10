<?php

namespace App\Filament\Resources\System\TeamResource\Pages;

use App\Filament\Resources\System\TeamResource;
use App\Models\System\Team;
use App\Services\System\TeamService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeam extends EditRecord
{
    protected static string $resource = TeamResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn (TeamService $service, Actions\DeleteAction $action, Team $record) =>
                    $service->preventTeamDeleteIf(action: $action, team: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['directors'] = $this->record->directors()
            ->pluck('id')
            ->toArray();

        $data['managers'] = $this->record->managers()
            ->pluck('id')
            ->toArray();

        $data['realtors'] = $this->record->realtors()
            ->pluck('id')
            ->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncDirectors();
        $this->syncManagers();
        $this->syncRealtors();
    }

    protected function syncDirectors(): void
    {
        $role = 1; // '1' - directors

        $data = collect($this->data['directors'])
            ->mapWithKeys(function ($id) use ($role) {
                return [$id => ['role' => $role]];
            })
            ->all();

        // Attach directors
        $this->record->directors()
            ->wherePivot('role', $role)
            ->sync($data);
    }

    protected function syncManagers(): void
    {
        $role = 2; // '2' - managers

        $data = collect($this->data['managers'])
            ->mapWithKeys(function ($id) use ($role) {
                return [$id => ['role' => $role]];
            })
            ->all();

        // Attach managers
        $this->record->managers()
            ->wherePivot('role', $role)
            ->sync($data);
    }

    protected function syncRealtors(): void
    {
        $role = 3; // '3' - realtors

        $data = collect($this->data['realtors'])
            ->mapWithKeys(function ($id) use ($role) {
                return [$id => ['role' => $role]];
            })
            ->all();

        // Attach realtors
        $this->record->realtors()
            ->wherePivot('role', $role)
            ->sync($data);
    }
}
