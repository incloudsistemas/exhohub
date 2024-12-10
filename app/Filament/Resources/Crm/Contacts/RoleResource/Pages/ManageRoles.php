<?php

namespace App\Filament\Resources\Crm\Contacts\RoleResource\Pages;

use App\Filament\Resources\Crm\Contacts\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRoles extends ManageRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
