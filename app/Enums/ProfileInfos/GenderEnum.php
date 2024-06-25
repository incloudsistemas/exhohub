<?php

namespace App\Enums\ProfileInfos;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum GenderEnum: string implements HasLabel
{
    use EnumHelper;

    case MASCULINE = 'M';
    case FEMININE  = 'F';

    public function getLabel(): string
    {
        return match ($this) {
            self::MASCULINE => 'Masculino',
            self::FEMININE  => 'Feminino',
        };
    }
}
