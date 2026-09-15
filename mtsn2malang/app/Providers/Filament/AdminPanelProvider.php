<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditProfile;
use App\Filament\Pages\ManageMaintenance;
use App\Filament\Pages\ManageSettings;
use App\Filament\Pages\ManageWordpressMigration;
use App\Filament\Pages\ManageWelcome;
use App\Filament\Resources\AchievementResource;
use App\Filament\Resources\BadgeAchievementResource;
use App\Filament\Resources\AlumniCoordinatorResource;
use App\Filament\Resources\ArchiveResource;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CommentResource;
use App\Filament\Resources\ComplaintResource;
use App\Filament\Resources\ExtracurricularResource;
use App\Filament\Resources\FacilityResource;
use App\Filament\Resources\GtkResource;
use App\Filament\Resources\HeadmasterHistoryResource;
use App\Filament\Resources\MediaResource;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\PageContentResource;
use App\Filament\Resources\PopupResource;
use App\Filament\Resources\PortalAppResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\RegistrationResource;
use App\Filament\Resources\SaranResource;
use App\Filament\Resources\SchoolLeaderResource;
use App\Filament\Resources\ServiceStandardResource;
use App\Filament\Resources\SocialMediaContentResource;
use App\Filament\Resources\SubscriberResource;
use App\Filament\Resources\SurveyLinkResource;
use App\Filament\Resources\TestimonialResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\ComplaintStatsWidget;
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
            ->login(Login::class)
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
            ->brandLogo(
                \App\Models\Setting::get('logo')
                    ? asset('storage/' . \App\Models\Setting::get('logo'))
                    : asset('images/logo.png')
            )
            ->brandLogoHeight('2.5rem')
            ->favicon(
                \App\Models\Setting::get('favicon')
                    ? asset('storage/' . \App\Models\Setting::get('favicon'))
                    : asset('images/favicon.png')
            )
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->profile(EditProfile::class, isSimple: false)
            ->userMenuItems([
                \Filament\Navigation\MenuItem::make()
                    ->label(fn () => \App\Support\RoleDisplay::labelWithEmoji(auth()->user()?->getRoleNames()->first()))
                    ->url('#')
                    ->icon('heroicon-o-shield-check'),
            ])
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
                MediaResource::class,
                CommentResource::class,
                PopupResource::class,
                CategoryResource::class,
                BannerResource::class,
                SchoolLeaderResource::class,
                GtkResource::class,
                HeadmasterHistoryResource::class,
                ExtracurricularResource::class,
                FacilityResource::class,
                AchievementResource::class,
                BadgeAchievementResource::class,
                TestimonialResource::class,
                PortalAppResource::class,
                ServiceStandardResource::class,
                ArchiveResource::class,
                ProgramResource::class,
                SocialMediaContentResource::class,
                SubscriberResource::class,
                RegistrationResource::class,
                AlumniCoordinatorResource::class,
                ComplaintResource::class,
                SaranResource::class,
                SurveyLinkResource::class,
                PageContentResource::class,
                UserResource::class,
            ])
            ->pages([
                Dashboard::class,
                ManageSettings::class,
                ManageMaintenance::class,
                ManageWordpressMigration::class,
                ManageWelcome::class,
            ])
            ->widgets([
                NewsStatsWidget::class,
                ComplaintStatsWidget::class,
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
