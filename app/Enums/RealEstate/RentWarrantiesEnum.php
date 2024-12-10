<?php

namespace App\Enums\RealEstate;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum RentWarrantiesEnum: string implements HasLabel
{
    use EnumHelper;

    case SECURITY_DEPOSIT     = 'Depósito de segurança';
    case GUARANTOR            = 'Fiador';
    case INSURANCE_GUARANTEE  = 'Garantia de seguro';
    case GUARANTEE_LETTER     = 'Carta de garantia';
    case CAPITALIZATION_BONDS = 'Títulos de capitalização';

    public function getLabel(): string
    {
        return match ($this) {
            self::SECURITY_DEPOSIT     => 'Depósito de segurança',
            self::GUARANTOR            => 'Fiador',
            self::INSURANCE_GUARANTEE  => 'Garantia de seguro',
            self::GUARANTEE_LETTER     => 'Carta de garantia',
            self::CAPITALIZATION_BONDS => 'Títulos de capitalização',
        };
    }
}
