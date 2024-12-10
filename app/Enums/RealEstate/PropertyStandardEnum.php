<?php

namespace App\Enums\RealEstate;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum PropertyStandardEnum: string implements HasLabel
{
    use EnumHelper;

    case ECONOMIC = '1';
    case MEDIUM   = '2';
    case HIGH     = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::RESIDENTIAL => 'Econômico',
            self::COMMERCIAL  => 'Médio Padrão',
            self::COMMERCIAL  => 'Alto Padrão',
        };
    }
}
