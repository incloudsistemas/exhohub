<?php

namespace App\Filament\Resources\System\RoleResource\Pages;

use App\Filament\Resources\System\RoleResource;
use App\Models\System\Role;
use App\Services\System\RoleService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn (RoleService $service, Actions\DeleteAction $action, Role $record) =>
                    $service->preventRoleDeleteIf(action: $action, role: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
