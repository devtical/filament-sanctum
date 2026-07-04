<?php

namespace Devtical\Sanctum\Tests\Feature;

use Devtical\Sanctum\Pages\Sanctum;
use Devtical\Sanctum\SanctumPlugin;
use Devtical\Sanctum\Tests\TestbenchTestCase;
use Devtical\Sanctum\Tests\TestUser;
use Filament\Actions\Action;
use Filament\Panel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Mockery;

class IntegrationTest extends TestbenchTestCase
{
    public function test_plugin_integration_works()
    {
        $plugin = $this->app->make(SanctumPlugin::class);

        $this->assertInstanceOf(SanctumPlugin::class, $plugin);
        $this->assertEquals('filament-sanctum', $plugin->getId());
    }

    public function test_plugin_registers_sanctum_page()
    {
        $plugin = $this->app->make(SanctumPlugin::class);

        $panel = Mockery::mock('Filament\Panel');
        $panel->shouldReceive('pages')
            ->once()
            ->with([Sanctum::class])
            ->andReturnSelf();

        $plugin->register($panel);

        $this->assertInstanceOf(SanctumPlugin::class, $plugin);
    }

    public function test_user_menu_integration()
    {
        Config::set('filament-sanctum.navigation.user_menu.enabled', true);
        Config::set('filament-sanctum.navigation.icon', 'heroicon-o-finger-print');

        $plugin = $this->app->make(SanctumPlugin::class);
        $menuItems = [];

        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')->andReturn('admin');
        $panel->shouldReceive('userMenuItems')
            ->once()
            ->withArgs(function (array $items) use (&$menuItems): bool {
                $menuItems = $items;

                return true;
            })
            ->andReturnSelf();

        $plugin->boot($panel);

        $this->assertCount(1, $menuItems);
        $this->assertInstanceOf(Action::class, $menuItems[0]);
        $this->assertSame('Token', $menuItems[0]->getLabel());
        $this->assertSame('heroicon-o-finger-print', $menuItems[0]->getIcon());

        Config::set('filament-sanctum.navigation.user_menu.enabled', false);

        $panel2 = Mockery::mock(Panel::class);
        $panel2->shouldNotReceive('userMenuItems');

        $plugin->boot($panel2);

        $this->assertInstanceOf(SanctumPlugin::class, $plugin);
    }

    public function test_slug_reads_from_config()
    {
        Config::set('filament-sanctum.navigation.slug', 'api-tokens');

        $this->assertSame('api-tokens', Sanctum::getDefaultSlug());
    }

    public function test_sanctum_page_url_resolves()
    {
        $panel = Panel::make()
            ->id('admin')
            ->path('admin');

        $this->assertSame('sanctum', Sanctum::getSlug($panel));
        $this->assertSame('sanctum', Sanctum::getRelativeRouteName($panel));
        $this->assertSame('/sanctum', Sanctum::getRoutePath($panel));
    }

    public function test_can_access_denied_when_gate_fails()
    {
        Config::set('filament-sanctum.authorization.enabled', true);
        Config::set('filament-sanctum.authorization.gate', 'manage-api-tokens');

        $user = TestUser::query()->create([
            'name' => 'Test User',
            'email' => 'gate-deny@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Gate::define('manage-api-tokens', fn () => false);

        $this->assertFalse(Sanctum::canAccess());
    }

    public function test_can_access_allowed_when_gate_passes()
    {
        Config::set('filament-sanctum.authorization.enabled', true);
        Config::set('filament-sanctum.authorization.gate', 'manage-api-tokens');

        $user = TestUser::query()->create([
            'name' => 'Test User',
            'email' => 'gate-allow@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Gate::define('manage-api-tokens', fn () => true);

        $this->assertTrue(Sanctum::canAccess());
    }

    public function test_can_create_and_revoke_token()
    {
        $user = TestUser::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $accessToken = $user->createToken('mobile-app', ['users:read']);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'mobile-app',
            'tokenable_id' => $user->id,
        ]);

        $accessToken->accessToken->delete();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => 'mobile-app',
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_sanctum_token_management_integration()
    {
        $page = new Sanctum;

        $this->assertInstanceOf(Sanctum::class, $page);
        $this->assertEquals('filament-sanctum::pages.sanctum', $page->getView());
        $this->assertEquals('Token', $page->getTitle());
        $this->assertEquals(-1, Sanctum::getNavigationSort());
    }

    public function test_sanctum_page_functional_behavior()
    {
        $page = new Sanctum;

        $this->assertEquals('Token', Sanctum::getNavigationLabel());
        $this->assertEquals('Token', $page->getTitle());
        $this->assertEquals(-1, Sanctum::getNavigationSort());
        $this->assertNull(Sanctum::getNavigationGroup());

        Config::set('filament-sanctum.navigation.sidebar_menu.enabled', true);
        $this->assertTrue(Sanctum::shouldRegisterNavigation());

        Config::set('filament-sanctum.navigation.sidebar_menu.enabled', false);
        $this->assertFalse(Sanctum::shouldRegisterNavigation());
    }

    public function test_configuration_integration()
    {
        $this->assertTrue($this->app['config']->has('filament-sanctum'));

        $config = $this->app['config']->get('filament-sanctum');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('navigation', $config);
        $this->assertArrayHasKey('abilities', $config);
        $this->assertArrayHasKey('authorization', $config);
    }

    public function test_views_integration()
    {
        $this->assertTrue($this->app['view']->exists('filament-sanctum::pages.sanctum'));
    }

    public function test_translations_integration()
    {
        $this->assertInstanceOf('Illuminate\Translation\Translator', $this->app['translator']);

        $translation = __('Sanctum', [], 'en');
        $this->assertNotEquals('Sanctum', $translation);
        $this->assertIsString($translation);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
