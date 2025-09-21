<?php

namespace Devtical\Sanctum\Tests\Unit;

use Devtical\Sanctum\SanctumPlugin;
use Devtical\Sanctum\SanctumServiceProvider;
use Devtical\Sanctum\Tests\TestbenchTestCase;

class ServiceProviderTest extends TestbenchTestCase
{
    public function test_package_can_be_loaded()
    {
        $this->assertTrue(class_exists(SanctumServiceProvider::class));
        $this->assertTrue(class_exists(SanctumPlugin::class));
    }

    public function test_service_provider_can_be_instantiated()
    {
        $provider = new SanctumServiceProvider($this->app);
        
        $this->assertInstanceOf(SanctumServiceProvider::class, $provider);
    }

    public function test_plugin_is_bound_as_singleton()
    {
        $plugin1 = $this->app->make(SanctumPlugin::class);
        $plugin2 = $this->app->make(SanctumPlugin::class);
        
        $this->assertSame($plugin1, $plugin2);
        $this->assertInstanceOf(SanctumPlugin::class, $plugin1);
    }

    public function test_package_resources_are_published()
    {
        // Test config is available
        $this->assertTrue($this->app['config']->has('filament-sanctum'));
        
        // Test views are available
        $this->assertTrue($this->app['view']->exists('filament-sanctum::pages.sanctum'));
        
        // Test translations are available
        $this->assertInstanceOf('Illuminate\Translation\Translator', $this->app['translator']);
    }
}
