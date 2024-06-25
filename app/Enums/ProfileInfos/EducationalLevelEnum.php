<?php

namespace App\Enums\ProfileInfos;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum EducationalLevelEnum: string implements HasLabel
{
    use EnumHelper;

    case ELEMENTARY    = '1';
    case HIGH_SCHOOL   = '2';
    case BACHELOR      = '3';
    case POST_GRADUATE = '4';
    case MASTER        = '5';
    case DOCTORATE     = '6';

    public function getLabel(): string
    {
        return match ($this) {
            self::ELEMENTARY    => 'Fundamental',
            self::HIGH_SCHOOL   => 'Médio',
            self::BACHELOR      => 'Superior',
            self::POST_GRADUATE => 'Pós-graduação',
            self::MASTER        => 'Mestrado',
            self::DOCTORATE     => 'Doutorado',
        };
    }
}
