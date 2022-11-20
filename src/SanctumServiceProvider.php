<?php

namespace Devtical\Sanctum;

use Devtical\Sanctum\Pages\Sanctum;
use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;

class SanctumServiceProvider extends PluginServiceProvider
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
            ->hasAssets('filament-sanctum')
            ->hasTranslations();
    }
}
