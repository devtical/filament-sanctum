<?php

namespace Devtical\Sanctum;

use Devtical\Sanctum\Pages\Sanctum;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Panel;

class SanctumPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

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
        if (config('filament-sanctum.navigation.user_menu.enabled')) {
            $panel->userMenuItems([
                Action::make('sanctum')
                    ->label(trans('Sanctum'))
                    ->url(fn (): string => Sanctum::getUrl(panel: $panel->getId()))
                    ->icon(config('filament-sanctum.navigation.icon', 'heroicon-o-finger-print')),
            ]);
        }
    }
}
