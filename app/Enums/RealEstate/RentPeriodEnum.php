<?php

namespace App\Enums\RealEstate;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum RentPeriodEnum: string implements HasLabel
{
    use EnumHelper;

    case DAILY     = '1';
    case WEEKLY    = '2';
    case MONTHLY   = '3';
    case QUARTERLY = '4';
    case YEARLY    = '5';

    public function getLabel(): string
    {
        return match ($this) {
            self::DAILY     => 'Diário',
            self::WEEKLY    => 'Semanal',
            self::MONTHLY   => 'Mensal',
            self::QUARTERLY => 'Trimestral',
            self::YEARLY    => 'Anual',
        };
    }
}
