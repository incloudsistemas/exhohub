<?php

namespace App\Traits;

trait EnumHelper
{
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabels(): array
    {
        return array_map(fn ($case) => $case->getLabel(), self::cases());
    }

    public static function getAssociativeArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn ($case) => $case->getLabel(), self::cases())
        );
    }
}
