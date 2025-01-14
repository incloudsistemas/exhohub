<?php

namespace App\Enums\Financial;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum BankAccountTypeEnum: string implements HasLabel
{
    use EnumHelper;

    case LEGAL_ENTITY = '1';
    case INDIVIDUAL   = '2';

    public function getLabel(): string
    {
        return match ($this) {
            self::LEGAL_ENTITY => 'Conta Pessoa Jurídica (PJ)',
            self::INDIVIDUAL   => 'Conta Pessoa Física (PF)',
        };
    }
}
