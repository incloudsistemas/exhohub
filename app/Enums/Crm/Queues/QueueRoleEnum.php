<?php

namespace App\Enums\Crm\Queues;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum QueueRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case WEBSITE = '1';
    case CANAL_PRO = '2';
    case META_ADS = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::WEBSITE   => 'Website',
            self::CANAL_PRO => 'Grupo OLX (CanalPro)',
            self::META_ADS  => 'Meta Ads',
        };
    }

    public function getSlug(): string
    {
        return match ($this) {
            self::WEBSITE   => 'website',
            self::CANAL_PRO => 'grupo-olx-canalpro',
            self::META_ADS  => 'meta-ads',
        };
    }
}
