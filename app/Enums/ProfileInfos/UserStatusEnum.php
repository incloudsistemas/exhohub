<?php

namespace App\Enums\ProfileInfos;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserStatusEnum: string implements HasLabel, HasColor
{
    case ACTIVE   = '1';
    case PENDING  = '2';
    case INACTIVE = '0';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Ativo',
            self::PENDING  => 'Pendente',
            self::INACTIVE => 'Inativo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE   => 'success',
            self::PENDING  => 'warning',
            self::INACTIVE => 'danger',
        };
    }
}
