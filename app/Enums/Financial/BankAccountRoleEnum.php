<?php

namespace App\Enums\Financial;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum BankAccountRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case CURRENT_ACCOUNT = '1';
    case SAVINGS         = '2';
    case CREDIT_CARD     = '3';
    case WALLET          = '4';
    case BOX             = '5';
    case INVESTMENTS     = '6';
    case OTHERS          = '7';

    public function getLabel(): string
    {
        return match ($this) {
            self::CURRENT_ACCOUNT => 'Conta corrente',
            self::SAVINGS         => 'Poupança',
            self::CREDIT_CARD     => 'Cartão de crédito',
            self::WALLET          => 'Carteira',
            self::BOX             => 'Caixa',
            self::INVESTMENTS     => 'Investimentos',
            self::OTHERS          => 'Outros',
        };
    }
}
