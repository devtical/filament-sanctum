<?php

namespace Devtical\Sanctum\Tests\Unit;

use Devtical\Sanctum\Tests\TestbenchTestCase;

class ConfigurationTest extends TestbenchTestCase
{
    public function test_config_file_is_loaded()
    {
        $this->assertTrue($this->app['config']->has('filament-sanctum'));
        $this->assertIsArray($this->app['config']->get('filament-sanctum'));
    }

    public function test_navigation_configuration()
    {
        $config = $this->app['config']->get('filament-sanctum.navigation');
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('slug', $config);
        $this->assertArrayHasKey('icon', $config);
        $this->assertArrayHasKey('sidebar_menu', $config);
        $this->assertArrayHasKey('user_menu', $config);
        
        // Test that config values exist and are of correct type
        $this->assertIsString($config['slug']);
        $this->assertIsString($config['icon']);
        $this->assertNotEmpty($config['slug']);
        $this->assertNotEmpty($config['icon']);
    }

    public function test_sidebar_menu_configuration()
    {
        $sidebarMenu = $this->app['config']->get('filament-sanctum.navigation.sidebar_menu');
        
        $this->assertIsArray($sidebarMenu);
        $this->assertArrayHasKey('enabled', $sidebarMenu);
        $this->assertArrayHasKey('sort', $sidebarMenu);
        $this->assertArrayHasKey('group', $sidebarMenu);
        
        // Test that config values exist and are of correct type
        $this->assertIsBool($sidebarMenu['enabled']);
        $this->assertIsInt($sidebarMenu['sort']);
        $this->assertTrue(is_null($sidebarMenu['group']) || is_string($sidebarMenu['group']));
    }

    public function test_user_menu_configuration()
    {
        $userMenu = $this->app['config']->get('filament-sanctum.navigation.user_menu');
        
        $this->assertIsArray($userMenu);
        $this->assertArrayHasKey('enabled', $userMenu);
        $this->assertIsBool($userMenu['enabled']);
    }

    public function test_abilities_configuration()
    {
        $abilities = $this->app['config']->get('filament-sanctum.abilities');
        
        $this->assertIsArray($abilities);
        $this->assertArrayHasKey('columns', $abilities);
        $this->assertArrayHasKey('list', $abilities);
        
        // Test that config values exist and are of correct type
        $this->assertIsInt($abilities['columns']);
        $this->assertGreaterThan(0, $abilities['columns']);
        $this->assertIsArray($abilities['list']);
    }

    public function test_config_can_be_published()
    {
        $this->artisan('vendor:publish', ['--tag' => 'filament-sanctum-config'])
            ->assertExitCode(0);
    }

    public function test_default_config_values_when_not_overridden()
    {
        // Test default values only when config is not overridden
        $config = $this->app['config']->get('filament-sanctum');
        
        // Test that default values exist and are reasonable
        $this->assertEquals('sanctum', $config['navigation']['slug']);
        $this->assertEquals('heroicon-o-finger-print', $config['navigation']['icon']);
        $this->assertFalse($config['navigation']['sidebar_menu']['enabled']);
        $this->assertEquals(-1, $config['navigation']['sidebar_menu']['sort']);
        $this->assertTrue($config['navigation']['user_menu']['enabled']);
        $this->assertEquals(4, $config['abilities']['columns']);
    }
}
