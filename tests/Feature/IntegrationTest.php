<?php

namespace Devtical\Sanctum\Tests\Feature;

use Devtical\Sanctum\Pages\Sanctum;
use Devtical\Sanctum\SanctumPlugin;
use Devtical\Sanctum\Tests\TestbenchTestCase;
use Illuminate\Support\Facades\Config;
use Mockery;

class IntegrationTest extends TestbenchTestCase
{
    public function test_plugin_integration_works()
    {
        // Test that plugin can be registered with Filament
        $plugin = $this->app->make(SanctumPlugin::class);
        
        $this->assertInstanceOf(SanctumPlugin::class, $plugin);
        $this->assertEquals('filament-sanctum', $plugin->getId());
    }

    public function test_plugin_registers_sanctum_page()
    {
        $plugin = $this->app->make(SanctumPlugin::class);
        
        $panel = \Mockery::mock('Filament\Panel');
        $panel->shouldReceive('pages')
            ->once()
            ->with([Sanctum::class])
            ->andReturnSelf();

        $plugin->register($panel);
        
        $this->assertInstanceOf(SanctumPlugin::class, $plugin);
    }


    public function test_user_menu_integration()
    {
        // Test user menu when enabled
        Config::set('filament-sanctum.navigation.user_menu.enabled', true);
        Config::set('filament-sanctum.navigation.slug', 'sanctum');
        Config::set('filament-sanctum.navigation.icon', 'heroicon-o-finger-print');
        
        $plugin = $this->app->make(SanctumPlugin::class);
        
        $panel = \Mockery::mock('Filament\Panel');
        $panel->shouldReceive('getPath')
            ->once()
            ->andReturn('/admin');
        $panel->shouldReceive('userMenuItems')
            ->once()
            ->andReturnSelf();
        
        $plugin->boot($panel);
        
        // Test user menu when disabled
        Config::set('filament-sanctum.navigation.user_menu.enabled', false);
        
        $panel2 = \Mockery::mock('Filament\Panel');
        $panel2->shouldNotReceive('userMenuItems');
        
        $plugin->boot($panel2);
        
        $this->assertInstanceOf(SanctumPlugin::class, $plugin);
    }

    public function test_sanctum_token_management_integration()
    {
        // Test that Sanctum page can be instantiated for token management
        $page = new Sanctum();
        
        $this->assertInstanceOf(Sanctum::class, $page);
        $this->assertEquals('filament-sanctum::pages.sanctum', $page->getView());
        
        // Test that page has correct properties for token management
        $this->assertEquals('Token', $page->getTitle());
        $this->assertEquals(-1, Sanctum::getNavigationSort());
    }

    public function test_sanctum_page_functional_behavior()
    {
        // Test functional behavior of Sanctum page
        $page = new Sanctum();
        
        // Test navigation properties
        $this->assertEquals('Token', Sanctum::getNavigationLabel());
        $this->assertEquals('Token', $page->getTitle());
        $this->assertEquals(-1, Sanctum::getNavigationSort());
        $this->assertNull(Sanctum::getNavigationGroup());
        
        // Test navigation registration based on config
        Config::set('filament-sanctum.navigation.sidebar_menu.enabled', true);
        $this->assertTrue(Sanctum::shouldRegisterNavigation());
        
        Config::set('filament-sanctum.navigation.sidebar_menu.enabled', false);
        $this->assertFalse(Sanctum::shouldRegisterNavigation());
    }

    public function test_configuration_integration()
    {
        // Test that configuration is properly integrated
        $this->assertTrue($this->app['config']->has('filament-sanctum'));
        
        $config = $this->app['config']->get('filament-sanctum');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('navigation', $config);
        $this->assertArrayHasKey('abilities', $config);
    }

    public function test_views_integration()
    {
        // Test that views are properly integrated
        $this->assertTrue($this->app['view']->exists('filament-sanctum::pages.sanctum'));
    }

    public function test_translations_integration()
    {
        // Test that translations are properly integrated
        $this->assertInstanceOf('Illuminate\Translation\Translator', $this->app['translator']);
        
        // Test that translation key resolves properly (not returning the key itself)
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
