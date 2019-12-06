<?php

namespace Korridor\LaravelComputedAttributes;

use Illuminate\Support\ServiceProvider;
use Korridor\LaravelComputedAttributes\Console\GenerateComputedAttributes;

/**
 * Class LaravelComputedAttributesServiceProvider.
 */
class LaravelComputedAttributesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\GenerateComputedAttributes::class,
            ]);
        }
    }
}
