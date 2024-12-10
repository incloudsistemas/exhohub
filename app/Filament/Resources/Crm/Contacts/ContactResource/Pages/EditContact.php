<?php

namespace App\Filament\Resources\Crm\Contacts\ContactResource\Pages;

use App\Filament\Resources\Crm\Contacts\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
