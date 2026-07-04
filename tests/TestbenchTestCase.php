<?php

namespace Devtical\Sanctum\Tests;

use Orchestra\Testbench\TestCase as Testbench;

abstract class TestbenchTestCase extends Testbench
{
    protected function getPackageProviders($app): array
    {
        return [
            \Devtical\Sanctum\SanctumServiceProvider::class,
        ];
    }
}
