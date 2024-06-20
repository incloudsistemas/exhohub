<?php

namespace App\Enums\ProfileInfos;

enum EducationalLevel: string
{
    case ELEMENTARY     = '1';
    case HIGH_SCHOOL    = '2';
    case BACHELOR       = '3';
    case POST_GRADUATE  = '4';
    case MASTER         = '5';
    case DOCTORATE      = '6';

    public function label(): string
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
