<?php

namespace App\Enums\ProfileInfos;

enum Gender: string
{
    case MASCULINE = 'M';
    case FEMININE  = 'F';

    public function label(): string
    {
        return match ($this) {
            self::MASCULINE => 'Masculino',
            self::FEMININE  => 'Feminino',
        };
    }

    public static function getArray(): array
    {
        return array_combine(
            array_map(fn ($enum) => $enum->value, self::cases()),
            array_map(fn ($enum) => $enum->label(), self::cases())
        );
    }

    public static function getIndexes(): array
    {
        return array_map(fn ($enum) => $enum->value, self::cases());
    }
}
