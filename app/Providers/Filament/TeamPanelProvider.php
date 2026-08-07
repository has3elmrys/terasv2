<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomEditProfile;
use App\Filament\Auth\CustomLogin;
use App\Filament\Auth\CustomRegistration;
use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Models\Team;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TeamPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('team')
            ->path('/')
            ->login(CustomLogin::class)
            ->profile(CustomEditProfile::class)
            ->tenant(Team::class, slugAttribute: 'slug')
            ->registration(CustomRegistration::class)
            ->tenantRegistration(RegisterTeam::class)
            ->resourceCreatePageRedirect('index')
            ->emailVerification()
            ->resourceEditPageRedirect('index')
            ->userMenuItems([
                MenuItem::make()
                        ->label('Admin Panel')
                        ->icon('heroicon-o-user-group')
                        ->url('/admin')
                        ->visible(fn () => auth()->user()?->hasRole('super_admin')),
                ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Team/Resources'), for: 'App\Filament\Team\Resources')
            ->discoverPages(in: app_path('Filament/Team/Pages'), for: 'App\Filament\Team\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Team/Widgets'), for: 'App\Filament\Team\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
