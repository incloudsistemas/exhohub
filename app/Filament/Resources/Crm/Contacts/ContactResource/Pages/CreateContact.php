<?php

namespace App\Filament\Resources\Crm\Contacts\ContactResource\Pages;

use App\Filament\Resources\Crm\Contacts\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
