<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Tests;

use Illuminate\Foundation\Application;
use Korridor\LaravelComputedAttributes\LaravelComputedAttributesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/TestEnvironment/Migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app->setBasePath(__DIR__ . '/TestEnvironment');
    }

    /**
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelComputedAttributesServiceProvider::class,
        ];
    }
}
