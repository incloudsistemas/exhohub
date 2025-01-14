<?php

namespace App\Enums\Financial;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum TransactionRepeatPaymentEnum: string implements HasLabel
{
    use EnumHelper;

    case IN_CASH     = '1';
    case INSTALLMENT = '2';
    case RECURRING   = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::IN_CASH     => 'À vista',
            self::INSTALLMENT => 'Parcelado',
            self::RECURRING   => 'Recorrente',
        };
    }
}
