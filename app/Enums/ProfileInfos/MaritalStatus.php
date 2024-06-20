<?php

namespace App\Enums\ProfileInfos;

enum MaritalStatus: string
{
    case SINGLE    = '1';
    case MARRIED   = '2';
    case DIVORCED  = '3';
    case WIDOWED   = '4';
    case SEPARATED = '5';
    case PARTNER   = '6';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE    => 'Solteiro(a)',
            self::MARRIED   => 'Casado(a)',
            self::DIVORCED  => 'Divorciado(a)',
            self::WIDOWED   => 'Viúvo(a)',
            self::SEPARATED => 'Separado(a)',
            self::PARTNER   => 'Companheiro(a)',
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
