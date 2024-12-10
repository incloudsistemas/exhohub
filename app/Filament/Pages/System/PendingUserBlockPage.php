<?php

namespace App\Filament\Pages\System;

use Filament\Pages\Concerns;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;

class PendingUserBlockPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = 'Cadastro aguardando aprovação.';

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static string $view = 'filament.pages.system.pending-user-block-page';

    public function mount(): void
    {
        // parent::mount();

        if ((int) auth()->user()->status->value === 1) {
            $route = Dashboard::getUrl();

            $this->redirect($route);
        }
    }

    public function hasLogo(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        return true;
    }
}
