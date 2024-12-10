<?php

namespace App\Enums\Cms;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum TestimonialRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case TEXT  = '1';
    case IMAGE = '2';
    case VIDEO = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::TEXT  => 'Texto',
            self::IMAGE => 'Imagem',
            self::VIDEO => 'Vídeo',
        };
    }
}
