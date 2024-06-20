<?php

namespace App\Enums;

enum DefaultStatus: string
{
    case ACTIVE   = '1';
    case INACTIVE = '0';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Ativo',
            self::INACTIVE => 'Inativo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE   => 'success',
            self::INACTIVE => 'danger',
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

    public static function getColor(string $status): ?string
    {
        return self::tryFrom($status)
            ?->color();
    }

    // public static function getColorByLabel(string $label): ?string
    // {
    //     foreach (self::cases() as $case) {
    //         if ($case->label() === $label) {
    //             return $case->color();
    //         }
    //     }
    //     return null;
    // }
}
