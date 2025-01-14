<?php

namespace App\Providers\Filament;

use App\Filament\Pages\System\CreateRealtor;
use App\Filament\Pages\System\EditProfile;
use App\Filament\Widgets\AppInfoWidget;
use App\Http\Middleware\PendingUserBlockAccess;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation;
use Filament\Pages;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/admin')
            ->registration(CreateRealtor::class)
            // ->emailVerification(EmailVerificationPrompt::class)
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Teal,
            ])
            ->favicon(url: asset('images/favicon.ico'))
            ->brandLogo(asset('images/i2c-logo.png'))
            ->darkModeBrandLogo(asset('images/i2c-logo-dark.png'))
            ->profile(EditProfile::class)
            ->userMenuItems([
                'profile' => Navigation\MenuItem::make()
                    ->label('Meu Perfil'),
                Navigation\MenuItem::make()
                    ->label('Website')
                    ->url('/')
                    ->icon('heroicon-o-globe-alt'),
                'logout' => Navigation\MenuItem::make()
                    ->label('Sair'),
            ])
            ->navigationGroups([
                Navigation\NavigationGroup::make()
                    ->label('CRM'),
                // Navigation\NavigationGroup::make()
                //     ->label('Financeiro'),
                Navigation\NavigationGroup::make()
                    ->label('Imóveis'),
                Navigation\NavigationGroup::make()
                    ->label('CMS & Marketing'),
                Navigation\NavigationGroup::make()
                    ->label('Financeiro'),
                Navigation\NavigationGroup::make()
                    ->label('Suporte'),
                Navigation\NavigationGroup::make()
                    ->label('Sistema'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                AppInfoWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,

                PendingUserBlockAccess::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
