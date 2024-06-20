<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AppInfoWidget extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.app-info-widget';

}
