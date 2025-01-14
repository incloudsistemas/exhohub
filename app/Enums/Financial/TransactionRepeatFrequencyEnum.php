<?php

namespace App\Enums\Financial;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum TransactionRepeatFrequencyEnum: string implements HasLabel
{
    use EnumHelper;

    case DAILY       = '1';
    case WEEKLY      = '2';
    case MONTHLY     = '3';
    case BIMONTHLY   = '4';
    case QUATERNALY  = '5';
    case HALF_YEARLY = '6';
    case ANNUAL      = '7';
    case WEEK_DAYS   = '8';

    public function getLabel(): string
    {
        return match ($this) {
            self::DAILY       => 'Diário',
            self::WEEKLY      => 'Semanal',
            self::MONTHLY     => 'Mensal',
            self::BIMONTHLY   => 'Bimestral',
            self::QUATERNALY  => 'Trimestral',
            self::HALF_YEARLY => 'Semestral',
            self::ANNUAL      => 'Anual',
            self::WEEK_DAYS   => 'Todos os dias da semana (Seg - Sex)',
        };
    }
}
