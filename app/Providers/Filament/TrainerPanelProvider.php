<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AttendanceRegister;
use App\Filament\Pages\TakeAttendance;
use App\Filament\Trainer\Widgets\MyTodayClasses;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

/** Instructor portal at /trainer: own classes, students and attendance only (see User::canAccessPanel). */
class TrainerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('trainer')
            ->path('trainer')
            ->login()
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('IT Security Nepal · Instructors')
            ->brandLogo(asset('images/logo-itsn.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/favicon.png'))
            ->colors([
                'primary' => Color::hex('#003275'),
                'info' => Color::hex('#1f5bb8'),
                'warning' => Color::hex('#f9b233'),
            ])
            ->font('Inter', url: asset('fonts/admin/inter.css'), provider: LocalFontProvider::class)
            ->darkMode(false)
            ->discoverResources(in: app_path('Filament/Trainer/Resources'), for: 'App\Filament\Trainer\Resources')
            ->pages([Dashboard::class, TakeAttendance::class, AttendanceRegister::class])
            ->widgets([MyTodayClasses::class])
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
