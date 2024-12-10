<?php

namespace App\Enums\RealEstate;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum PropertyUsageEnum: string implements HasLabel
{
    use EnumHelper;

    case RESIDENTIAL = '1';
    case COMMERCIAL  = '2';

    public function getLabel(): string
    {
        return match ($this) {
            self::RESIDENTIAL => 'Residencial',
            self::COMMERCIAL  => 'Comercial',
        };
    }

    public function getSlug(): string
    {
        return match ($this) {
            self::RESIDENTIAL => 'residencial',
            self::COMMERCIAL  => 'comercial',
        };
    }
}
