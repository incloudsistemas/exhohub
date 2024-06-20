<?php

namespace App\Enums\ProfileInfos;

enum UserStatus: string
{
    case ACTIVE   = '1';
    case PENDING  = '2';
    case INACTIVE = '0';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Ativo',
            self::PENDING  => 'Pendente',
            self::INACTIVE => 'Inativo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE   => 'success',
            self::PENDING  => 'warning',
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
}
