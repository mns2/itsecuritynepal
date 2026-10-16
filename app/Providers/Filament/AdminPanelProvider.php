<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\EnquiryStats;
use App\Filament\Widgets\LatestEnquiries;
use App\Filament\Widgets\LeadsByAudience;
use App\Filament\Widgets\LeadsChart;
use App\Filament\Widgets\OngoingClasses;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->brandName('IT Security Nepal')
            ->brandLogo(asset('images/logo-itsn.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/favicon.png'))
            ->colors([
                'primary' => Color::hex('#003275'),
                'info' => Color::hex('#1f5bb8'),
                'warning' => Color::hex('#f9b233'),
            ])
            ->font('Inter')
            ->darkMode(false) // brand rule: no dark theme, admin included
            ->navigationGroups(['Leads', 'Training', 'Catalogue', 'Content', 'Settings'])
            ->navigationItems([
                NavigationItem::make('View website')->url('/', shouldOpenInNewTab: true)->icon('heroicon-o-globe-alt')->group('Settings')->sort(99),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->widgets([EnquiryStats::class, LeadsChart::class, LeadsByAudience::class, OngoingClasses::class, LatestEnquiries::class])
            ->databaseNotifications(false)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
