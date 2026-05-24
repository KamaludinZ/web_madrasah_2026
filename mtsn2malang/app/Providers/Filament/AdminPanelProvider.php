<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManageMaintenance;
use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\AchievementResource;
use App\Filament\Resources\ArchiveResource;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ComplaintResource;
use App\Filament\Resources\ExtracurricularResource;
use App\Filament\Resources\FacilityResource;
use App\Filament\Resources\GtkResource;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\PageContentResource;
use App\Filament\Resources\PortalAppResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\RegistrationResource;
use App\Filament\Resources\ServiceStandardResource;
use App\Filament\Resources\TestimonialResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\NewsStatsWidget;
use App\Filament\Widgets\PageViewsWidget;
use App\Filament\Widgets\RecentNewsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
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
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::hex('#1a7a3c'),
                'secondary' => Color::hex('#f97316'),
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Orange,
            ])
            ->brandName('MTsN 2 Kota Malang')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/favicon.png'))
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Konten')->icon('heroicon-o-document-text'),
                NavigationGroup::make('Profil Sekolah')->icon('heroicon-o-building-library'),
                NavigationGroup::make('Layanan & PPID')->icon('heroicon-o-clipboard-document-list'),
                NavigationGroup::make('Program')->icon('heroicon-o-academic-cap'),
                NavigationGroup::make('Ma\'had')->icon('heroicon-o-home-modern'),
                NavigationGroup::make('Pengaduan & Survei')->icon('heroicon-o-chat-bubble-left-right'),
                NavigationGroup::make('Pengaturan')->icon('heroicon-o-cog-6-tooth'),
            ])
            ->resources([
                NewsResource::class,
                CategoryResource::class,
                BannerResource::class,
                GtkResource::class,
                ExtracurricularResource::class,
                FacilityResource::class,
                AchievementResource::class,
                TestimonialResource::class,
                PortalAppResource::class,
                ServiceStandardResource::class,
                ArchiveResource::class,
                ProgramResource::class,
                RegistrationResource::class,
                ComplaintResource::class,
                PageContentResource::class,
                UserResource::class,
            ])
            ->pages([
                Dashboard::class,
                ManageSettings::class,
                ManageMaintenance::class,
            ])
            ->widgets([
                NewsStatsWidget::class,
                PageViewsWidget::class,
                RecentNewsWidget::class,
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
