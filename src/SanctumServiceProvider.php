<?php

namespace Devtical\Sanctum;

use Devtical\Sanctum\Pages\Sanctum;
use Filament\FilamentServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Filament\Navigation\UserMenuItem;

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
            UserMenuItem::make()
                ->label(trans(config('filament-sanctum.label')))
                ->url(route('filament.pages.'.config('filament-sanctum.slug')))
                ->icon('heroicon-s-cog'),
        ] : [];
    }
}
