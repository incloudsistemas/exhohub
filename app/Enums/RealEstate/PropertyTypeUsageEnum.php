<?php

namespace App\Enums\RealEstate;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum PropertyTypeUsageEnum: string implements HasLabel
{
    use EnumHelper;

    case RESIDENTIAL            = '1';
    case COMMERCIAL             = '2';
    case RESIDENTIAL_COMMERCIAL = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::RESIDENTIAL            => 'Residencial',
            self::COMMERCIAL             => 'Comercial',
            self::RESIDENTIAL_COMMERCIAL => 'Residencial e Comercial',
        };
    }
}
