<?php

namespace App\Filament\Resources\Financial\ReceivableTransactionResource\Pages;

use App\Filament\Resources\Financial\ReceivableTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReceivableTransaction extends EditRecord
{
    protected static string $resource = ReceivableTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
