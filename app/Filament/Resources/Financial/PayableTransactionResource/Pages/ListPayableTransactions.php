<?php

namespace App\Filament\Resources\Financial\PayableTransactionResource\Pages;

use App\Filament\Resources\Financial\PayableTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayableTransactions extends ListRecords
{
    protected static string $resource = PayableTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
