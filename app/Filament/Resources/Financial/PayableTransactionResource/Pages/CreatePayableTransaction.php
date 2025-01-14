<?php

namespace App\Filament\Resources\Financial\PayableTransactionResource\Pages;

use App\Filament\Resources\Financial\PayableTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayableTransaction extends CreateRecord
{
    protected static string $resource = PayableTransactionResource::class;
}
