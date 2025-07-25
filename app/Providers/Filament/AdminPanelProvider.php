<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\LoginPage;
use App\Filament\Pages\ProfileInformation;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Exception;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
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
    public function boot(): void
    {
        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Amber,
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);

        if (! $this->app->isProduction()) {
            FilamentView::registerRenderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn () => view('components.app-environment'),
            );
        }
    }

    /**
     * @throws Exception
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(LoginPage::class)
            ->profile(ProfileInformation::class, isSimple: false)
            ->font('Poppins')
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                GlobalSearchModalPlugin::make()
                    ->maxWidth(MaxWidth::FourExtraLarge),
            ])
            ->navigationItems([
                NavigationItem::make('OB Project Tracker')
                    ->icon('codicon-project')
                    ->url(fn () => 'https://projects.objectbright.com/')
                    ->openUrlInNewTab()
                    ->group('Task Tracker')
                    ->sort(-1),

                NavigationItem::make('System Logs')
                    ->icon('fluentui-clipboard-text-edit-20-o')
                    ->sort(0)
                    ->url(fn () => route('log-viewer.index'))
                    ->openUrlInNewTab()
                    ->hidden(fn () => ! auth()->user()?->isSuperAdmin())
                    ->group('System Management'),

                NavigationItem::make('Performance Metrics')
                    ->icon('fluentui-desktop-pulse-28-o')
                    ->sort(1)
                    ->url(fn () => route('pulse'))
                    ->openUrlInNewTab()
                    ->hidden(fn () => ! auth()->user()?->isSuperAdmin())
                    ->group('System Management'),
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldKeyBindingSuffix()
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
