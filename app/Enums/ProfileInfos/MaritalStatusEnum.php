<?php

namespace App\Enums\ProfileInfos;

use Filament\Support\Contracts\HasLabel;

enum MaritalStatusEnum: string implements HasLabel
{
    case SINGLE    = '1';
    case MARRIED   = '2';
    case DIVORCED  = '3';
    case WIDOWED   = '4';
    case SEPARATED = '5';
    case PARTNER   = '6';

    public function getLabel(): string
    {
        return match ($this) {
            self::SINGLE    => 'Solteiro(a)',
            self::MARRIED   => 'Casado(a)',
            self::DIVORCED  => 'Divorciado(a)',
            self::WIDOWED   => 'Viúvo(a)',
            self::SEPARATED => 'Separado(a)',
            self::PARTNER   => 'Companheiro(a)',
        };
    }
}
