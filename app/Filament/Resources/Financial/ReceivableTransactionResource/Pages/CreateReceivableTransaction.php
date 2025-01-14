<?php

namespace App\Filament\Resources\Financial\ReceivableTransactionResource\Pages;

use App\Filament\Resources\Financial\ReceivableTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReceivableTransaction extends CreateRecord
{
    protected static string $resource = ReceivableTransactionResource::class;
}
