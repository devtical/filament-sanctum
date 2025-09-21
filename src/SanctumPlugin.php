<?php

namespace Devtical\Sanctum;

use Devtical\Sanctum\Pages\Sanctum;
use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;

class SanctumPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-sanctum';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                Sanctum::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // Register user menu items if enabled
        if (config('filament-sanctum.navigation.user_menu.enabled')) {
            $panel->userMenuItems([
                MenuItem::make()
                    ->label(trans('Sanctum'))
                    ->url($panel->getPath().'/'.config('filament-sanctum.navigation.slug'))
                    ->icon(config('filament-sanctum.navigation.icon', 'heroicon-o-finger-print')),
            ]);
        }
    }
}
