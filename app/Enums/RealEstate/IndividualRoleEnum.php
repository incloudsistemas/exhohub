<?php

namespace App\Enums\RealEstate;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum IndividualRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case SALE      = '1';
    case RENT      = '2';
    case SALE_RENT = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::SALE      => 'Venda',
            self::RENT      => 'Aluguel',
            self::SALE_RENT => 'Venda e Aluguel',
        };
    }

    public function getSlug(): string
    {
        return match ($this) {
            self::SALE,
            self::SALE_RENT => 'a-venda',
            self::RENT      => 'para-alugar',
        };
    }
}
