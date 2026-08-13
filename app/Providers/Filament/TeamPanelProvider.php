<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomEditProfile;
use App\Filament\Auth\CustomLogin;
use App\Filament\Auth\CustomRegistration;
use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Filament\Team\Pages\TeamDashboard;
use App\Filament\Team\Resources\Banks\BankResource;
use App\Filament\Team\Resources\Transactions\TransactionResource;
use App\Models\Team;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Hammadzafar05\FilamentMobilePreset\FilamentMobilePresetPlugin;
use Hammadzafar05\MobileBottomNav\MobileBottomNav;
use Hammadzafar05\MobileBottomNav\MobileBottomNavItem;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
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
            ->brandName('Teras')
            // ->brandLogo(asset('icon/logo/teras-icon-512.png'))
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
             ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('partials.pwa-head'))
            ->renderHook(PanelsRenderHook::BODY_END, fn () => view('partials.pwa-scripts'))
            ->discoverResources(in: app_path('Filament/Team/Resources'), for: 'App\Filament\Team\Resources')
            ->discoverPages(in: app_path('Filament/Team/Pages'), for: 'App\Filament\Team\Pages')
            ->pages([
                // Dashboard::class,
                TeamDashboard::class
            ])
            ->discoverWidgets(in: app_path('Filament/Team/Widgets'), for: 'App\Filament\Team\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
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
            ->plugin(
                FilamentMobilePresetPlugin::make()
                ->bottomNav(
            MobileBottomNav::make()
                // ->moreButton(false)
                ->items([
                    MobileBottomNavItem::make('Transactions')
                        ->icon('heroicon-o-receipt-percent')
                        ->url(fn () => TransactionResource::getUrl()),

                    MobileBottomNavItem::make('Dashboard')
                        ->icon('heroicon-o-home')
                        ->url(fn () => TeamDashboard::getUrl()),

                    // MobileBottomNavItem::make('Add')
                    //     ->icon('heroicon-o-plus-circle')
                    //     ->url(fn () => TransactionResource::getUrl().'?quickAdd=1'),

                    MobileBottomNavItem::make('Banks')
                        ->icon('heroicon-o-building-library')
                        ->url(fn () => BankResource::getUrl()),
                ])

        )
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }



}
