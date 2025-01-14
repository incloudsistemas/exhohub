<?php

namespace App\Enums\Financial;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum CategoryRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case REVENUE = '1';
    case EXPENSE = '2';

    public function getLabel(): string
    {
        return match ($this) {
            self::REVENUE => 'Categorias de receita',
            self::EXPENSE => 'Categorias de despesa',
        };
    }
}
