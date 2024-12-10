<?php

namespace App\Enums\Cms;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum PostSliderRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case DEFAULT = '1';
    case VIDEO   = '2';
    case YOUTUBE = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT => 'Padrão',
            self::VIDEO   => 'Vídeo',
            self::YOUTUBE => 'Youtube',
        };
    }
}
