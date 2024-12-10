<?php

namespace App\Enums\System;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum CreciControlStageRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case TRAINEE = '1';
    case INTERN  = '2';
    case REALTOR = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::TRAINEE => 'Trainee',
            self::INTERN  => 'Estagiário',
            self::REALTOR => 'Corretor Pleno',
        };
    }
}
