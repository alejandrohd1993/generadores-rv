<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->databaseNotifications()
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon('https://rvproducciones.com.co/wp-content/uploads/2023/01/LOGO-2.png')
            ->brandLogo('https://rvproducciones.com.co/wp-content/uploads/2023/01/LOGO-2.png')
            ->brandName('Generadores RBA')
            ->brandLogoHeight('3rem')
            ->font('Montserrat')
            ->colors([
                'primary' => '#E02317',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\ServicesCalendar::class,
                \App\Filament\Pages\ServiceReports::class,
                \App\Filament\Pages\GeneratorReports::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ])
            // ->plugins([
            //     \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            // ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
