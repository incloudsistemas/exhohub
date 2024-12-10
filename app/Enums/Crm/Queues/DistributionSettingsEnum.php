<?php

namespace App\Enums\Crm\Queues;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum DistributionSettingsEnum: string implements HasLabel
{
    use EnumHelper;

    case FOR_CAPTORS = '1';
    case ROUND_ROBIN = '2';
    case PERFORMANCE_PRIORITY = '3';
    case AVAILABILITY = '4';

    public function getLabel(): string
    {
        return match ($this) {
            self::FOR_CAPTORS          => 'Para os captadores',
            self::ROUND_ROBIN          => 'Distribuição alternada',
            self::PERFORMANCE_PRIORITY => 'Prioridade por performance',
            self::AVAILABILITY         => 'Disponibilidade',
        };
    }

    public static function getOptions(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => $case->getLabel(), self::cases())
        );
    }
}
