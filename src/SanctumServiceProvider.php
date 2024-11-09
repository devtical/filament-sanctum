<?php

namespace Eightygrit\FilamentSanctum;

use Eightygrit\FilamentSanctum\Pages\Sanctum;
use Filament\FilamentServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Filament\Navigation\MenuItem;

class SanctumServiceProvider extends FilamentServiceProvider
{
    protected array $pages = [
        Sanctum::class,
    ];

    protected array $styles = [
        'filament-sanctum' => __DIR__.'/../resources/dist/app.css',
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-sanctum')
            ->hasViews()
            ->hasConfigFile()
            ->hasAssets('filament-sanctum')
            ->hasTranslations();
    }

    protected function getUserMenuItems(): array
    {
        return config('filament-sanctum.user_menu') ? [
            MenuItem::make()
                ->label(trans(config('filament-sanctum.label')))
                ->url(route('filament.pages.'.config('filament-sanctum.slug')))
                ->icon('heroicon-s-cog'),
        ] : [];
    }
}
