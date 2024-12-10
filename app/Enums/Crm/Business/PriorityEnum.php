<?php

namespace App\Enums\Crm\Business;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PriorityEnum: string implements HasLabel, HasColor
{
    use EnumHelper;

    case LOW    = '1';
    case MEDIUM = '2';
    case HIGHT  = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW    => 'Baixa',
            self::MEDIUM => 'Média',
            self::HIGHT  => 'Alta',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LOW    => 'success',
            self::MEDIUM => 'warning',
            self::HIGHT  => 'danger',
        };
    }
}
