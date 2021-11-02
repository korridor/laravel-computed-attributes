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
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/computed-attributes.php' => config_path('computed-attributes.php'),
            ], 'computed-attributes');
            $this->commands([
                Console\GenerateComputedAttributes::class,
                Console\ValidateComputedAttributes::class,
            ]);
        }
        $this->app->bind(ModelAttributeParser::class, function () {
            return new ModelAttributeParser();
        });
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(
                __DIR__.'/../config/computed-attributes.php',
                'computed-attributes'
            );
        }
    }
}
