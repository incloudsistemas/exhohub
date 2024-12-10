<?php

namespace App\Enums\Crm\Queues;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum PropertiesSettingsEnum: string implements HasLabel
{
    use EnumHelper;

    case CUSTOMIZE_PROPERTIES = '1';
    case ALL_PROPERTIES = '2';
    case ONLY_FOR_SALE = '3';
    case ONLY_FOR_RENT = '4';
    case ONLY_ENTERPRISES = '5';

    public function getLabel(): string
    {
        return match ($this) {
            self::CUSTOMIZE_PROPERTIES => 'Customizar os imóveis',
            self::ALL_PROPERTIES       => 'Todos os imóveis e empreendimentos',
            self::ONLY_FOR_SALE        => 'Todos os imóveis à venda',
            self::ONLY_FOR_RENT        => 'Todos os imóveis para alugar',
            self::ONLY_ENTERPRISES     => 'Todos os empreendimentos',
        };
    }
}
