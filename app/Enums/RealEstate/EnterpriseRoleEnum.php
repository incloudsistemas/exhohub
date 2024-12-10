<?php

namespace App\Enums\RealEstate;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum EnterpriseRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case IN_PLANT           = '1';
    case UNDER_CONSTRUCTION = '2';
    case READY_TO_LIVE      = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::IN_PLANT           => 'Na planta',
            self::UNDER_CONSTRUCTION => 'Em construção',
            self::READY_TO_LIVE      => 'Pronto para morar',
        };
    }

    public function getSlug(): string
    {
        return match ($this) {
            self::IN_PLANT           => 'na-planta',
            self::UNDER_CONSTRUCTION => 'em-construcao',
            self::READY_TO_LIVE      => 'pronto-para-morar',
        };
    }
}
