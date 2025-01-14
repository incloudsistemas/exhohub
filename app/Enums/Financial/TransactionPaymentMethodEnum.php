<?php

namespace App\Enums\Financial;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum TransactionPaymentMethodEnum: string implements HasLabel
{
    use EnumHelper;

    case CASH          = '1';
    case PIX           = '2';
    case CHECK         = '3';
    case BANK_TRANSFER = '4';
    case DEBIT_CARD    = '5';
    case CREDIT_CARD   = '6';
    case OTHERS        = '7';

    public function getLabel(): string
    {
        return match ($this) {
            self::CASH          => 'Dinheiro',
            self::PIX           => 'Pix',
            self::CHECK         => 'Cheque',
            self::BANK_TRANSFER => 'Transferência bancária',
            self::DEBIT_CARD    => 'Cartão de débito',
            self::CREDIT_CARD   => 'Cartão de crédito',
            self::OTHERS        => 'Outros',
        };
    }
}
