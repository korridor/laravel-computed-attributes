<?php

namespace Korridor\LaravelComputedAttributes;

use Illuminate\Support\ServiceProvider;
use Korridor\LaravelComputedAttributes\Parser\ModelAttributeParser;

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
            $this->publishes([
                __DIR__.'/../config/computed-attributes.php' => config_path('computed-attributes.php'),
            ], 'computed-attributes-config');
            $this->commands([
                Console\GenerateComputedAttributes::class,
            ]);
        }
        $this->app->bind(ModelAttributeParser::class, function () {
            return new ModelAttributeParser();
        });
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(
                __DIR__.'/../config/computed-attributes.php',
                'computed-attributes-config'
            );
        }
    }
}
