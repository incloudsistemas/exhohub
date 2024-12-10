<?php

namespace App\Enums\Crm\Business;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum PropertiesInterestProfileRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case SALE       = '1';
    case RENT       = '2';
    case ENTERPRISE = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::SALE       => 'Venda',
            self::RENT       => 'Aluguel',
            self::ENTERPRISE => 'Lançamento',
        };
    }
}
