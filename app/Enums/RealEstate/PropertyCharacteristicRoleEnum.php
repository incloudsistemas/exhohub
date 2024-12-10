<?php

namespace App\Enums\RealEstate;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum PropertyCharacteristicRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case DIFFERENCES        = '1';
    case LEISURE_SPORTS     = '2';
    case SECURITY           = '3';
    case AMENITIES_SERVICES = '4';

    public function getLabel(): string
    {
        return match ($this) {
            self::DIFFERENCES        => 'Diferenciais',
            self::LEISURE_SPORTS     => 'Lazer e Esportes',
            self::SECURITY           => 'Segurança',
            self::AMENITIES_SERVICES => 'Comodidades e Serviços',
        };
    }
}
