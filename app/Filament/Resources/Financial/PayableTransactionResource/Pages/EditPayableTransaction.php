<?php

namespace App\Filament\Resources\Financial\PayableTransactionResource\Pages;

use App\Filament\Resources\Financial\PayableTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayableTransaction extends EditRecord
{
    protected static string $resource = PayableTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
