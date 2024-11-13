<?php

namespace EuBourne\LaravelPlugins;

use Illuminate\Support\ServiceProvider;

abstract class BaseServiceProvider extends ServiceProvider
{
    /**
     * List of providers to register
     *
     * @var array
     */
    protected array $providers = [];

    /**
     * List of console commands to register
     *
     * @var array
     */
    protected array $console = [];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerProviders();
        $this->registerCommands();
    }

    /**
     * Register providers
     */
    protected function registerProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            $this->app->register($providerClass);
        }
    }

    /**
     * Register Artisan console commands
     */
    protected function registerCommands(): void
    {
        $this->commands($this->console);
    }
}
