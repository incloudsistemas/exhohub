<?php

namespace App\Enums\Support;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketStatusEnum: string implements HasLabel, HasColor
{
    use EnumHelper;

    case WAITING  = '0';
    case OPEN     = '1';
    case FINISHED = '2';

    public function getLabel(): string
    {
        return match ($this) {
            self::WAITING  => 'Aguardando atendimento',
            self::OPEN     => 'Aberto',
            self::FINISHED => 'Finalizado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::WAITING  => 'warning',
            self::OPEN     => 'success',
            self::FINISHED => 'danger',
        };
    }
}
