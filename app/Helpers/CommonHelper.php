<?php

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

/**
 * Transforms the float string value into a int.
 *
 */
if (!function_exists('ConvertPtBrFloatStringToInt')) {
    function ConvertPtBrFloatStringToInt(mixed $value): int
    {
        $value = str_replace(".", "", $value);
        $value = str_replace(",", ".", $value);

        return round(floatval($value) * 100);
    }
}

/**
 * Transforms the int value into a float.
 *
 */
if (!function_exists('ConvertIntToFloat')) {
    function ConvertIntToFloat(mixed $value): float
    {
        // Transform the integer stored in the database into a float.
        return round(floatval($value) / 100, precision: 2);
    }
}

/**
 * Convert date from pt-br format to en.
 *
 */
if (!function_exists('ConvertPtBrToEnDate')) {
    function ConvertPtBrToEnDate(string $date): string
    {
        return date("Y-m-d", strtotime(str_replace('/', '-', $date)));
    }
}

/**
 * Convert date from pt-br format to en.
 *
 */
if (!function_exists('ConvertPtBrToEnDateTime')) {
    function ConvertPtBrToEnDateTime(string $date): string
    {
        return date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $date)));
    }
}

/**
 * Convert date from pt-br format to long/full.
 *
 */
if (!function_exists('ConvertPtBrToLongDate')) {
    function ConvertPtBrToLongDate(string $date): string
    {
        $weekday = [
            'Sunday'    => 'Domingo',
            'Monday'    => 'Segunda-Feira',
            'Tuesday'   => 'Terça-Feira',
            'Wednesday' => 'Quarta-Feira',
            'Thursday'  => 'Quinta-Feira',
            'Friday'    => 'Sexta-Feira',
            'Saturday'  => 'Sábado'
        ];

        $month = [
            'January'   => 'Janeiro',
            'February'  => 'Fevereiro',
            'March'     => 'Março',
            'April'     => 'Abril',
            'May'       => 'Maio',
            'June'      => 'Junho',
            'July'      => 'Julho',
            'August'    => 'Agosto',
            'September' => 'Setembro',
            'October'   => 'Outubro',
            'November'  => 'Novembro',
            'December'  => 'Dezembro'
        ];

        $dateFormat = date("l, d \d\e F \d\e Y", strtotime(str_replace('/', '-', $date)));

        foreach ($weekday as $en => $ptBr) {
            $dateFormat = str_replace($en, $ptBr, $dateFormat);
        }

        foreach ($month as $en => $ptBr) {
            $dateFormat = str_replace($en, $ptBr, $dateFormat);
        }

        return $dateFormat;
    }
}

/**
 * Convert date from en format to pt-br.
 *
 */
if (!function_exists('ConvertEnToPtBrDate')) {
    function ConvertEnToPtBrDate(string $date): string
    {
        return date("d/m/Y", strtotime($date));
    }
}

/**
 * Convert date from en format to pt-br.
 *
 */
if (!function_exists('ConvertEnToPtBrDateTime')) {
    function ConvertEnToPtBrDateTime(string $date, bool $showSeconds = false): string
    {
        if ($showSeconds) {
            return date("d/m/Y H:i:s", strtotime($date));
        }

        return date("d/m/Y H:i", strtotime($date));
    }
}

/**
 * Format the string in terms of characters.
 *
 */
if (!function_exists('FormatDateToGmtString')) {
    function FormatDateToGmtString(string $date): string
    {
        $date = new \DateTime($date);

        return $date->format('D M d Y H:i:s \G\M\TO (T)');
    }
}

/**
 * Limit the string in terms of characters.
 *
 */
if (!function_exists('LimitCharsFromString')) {
    function LimitCharsFromString(?string $string, int $numChars = 280): ?string
    {
        if (!$string) {
            return null;
        }

        if (mb_strlen($string, 'UTF-8') <= $numChars) {
            return $string;
        }

        $string = mb_substr($string, 0, $numChars, 'UTF-8') . '...';
        return $string;
    }
}

/**
 * Clear the variable, removing special characters, spaces, etc...
 *
 */
if (!function_exists('SanitizeVar')) {
    function SanitizeVar(string $string): string
    {
        $search = [
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];

        $string = preg_replace($search, $replace, $string);
        return $string;
    }
}

/**
 * Get the number abbreviation
 *
 */
if (!function_exists('AbbrNumberFormat')) {
    function AbbrNumberFormat(int $number): string
    {
        if ($number < 1000) {
            return Number::format($number, 0);
        }

        if ($number < 1000000) {
            return Number::format($number / 1000, 2) . 'k';
        }

        return Number::format($number / 1000000, 2) . 'm';
    };
}

/**
 * Get the path by url
 *
 */
if (!function_exists('GetUrlPath')) {
    function GetUrlPath(string $url): string
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path']; // Pegar o caminho da URL

        // Supondo que a parte '/storage/' esteja linkada a 'storage/app/public' no disco local
        $path = str_replace('/storage/', '', $path); // Remover o prefixo da URL

        // Obter o caminho absoluto no disco 'public'
        $disk = Storage::disk('public');
        $fullPath = $disk->path($path);

        return $fullPath;
    };
}

/**
 * Get the model type from morphMap
 *
 */
if (!function_exists('MorphMapByClass')) {
    function MorphMapByClass(string $model): string
    {
        $morphMap = Relation::morphMap();
        return array_search($model, $morphMap, true) ?: $model;
    }
}
