<?php

namespace Devtical\Sanctum\Tests;

use Devtical\Sanctum\SanctumPlugin;
use Devtical\Sanctum\SanctumServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Testbench;

abstract class TestbenchTestCase extends Testbench
{
    protected function getPackageProviders($app): array
    {
        return [
            SanctumServiceProvider::class,
            FilamentServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', TestUser::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set(
            'filament-sanctum',
            require dirname(__DIR__).'/config/filament-sanctum.php',
        );

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        $this->loadMigrationsFrom(
            dirname(__DIR__).'/vendor/laravel/sanctum/database/migrations'
        );

        Filament::registerPanel(
            Panel::make()
                ->default()
                ->id('admin')
                ->path('admin')
                ->plugin(SanctumPlugin::make()),
        );

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }
}

class TestUser extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password'];
}
