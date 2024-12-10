<?php

namespace App\Enums\Cms;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PostStatusEnum: string implements HasLabel, HasColor
{
    use EnumHelper;

    case ACTIVE   = '1';
    case DRAFT    = '2';
    case INACTIVE = '0';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Ativo',
            self::DRAFT    => 'Rascunho',
            self::INACTIVE => 'Inativo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE   => 'success',
            self::DRAFT    => 'warning',
            self::INACTIVE => 'danger',
        };
    }
}
