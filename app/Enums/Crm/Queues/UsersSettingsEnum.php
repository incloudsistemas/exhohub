<?php

namespace App\Enums\Crm\Queues;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum UsersSettingsEnum: string implements HasLabel
{
    use EnumHelper;

    case CUSTOMIZE_USERS = '1';
    case CUSTOMIZE_BY_AGENCIES = '2';
    case CUSTOMIZE_BY_TEAMS = '3';
    case ALL_USERS = '4';

    public function getLabel(): string
    {
        return match ($this) {
            self::CUSTOMIZE_USERS       => 'Customizar os usuários',
            self::CUSTOMIZE_BY_AGENCIES => 'Customizar por agências',
            self::CUSTOMIZE_BY_TEAMS    => 'Customizar por equipes',
            self::ALL_USERS             => 'Todos os usuários',
        };
    }
}
