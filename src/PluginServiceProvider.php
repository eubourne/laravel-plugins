<?php

namespace EuBourne\LaravelPlugins;

use EuBourne\LaravelPlugins\Console\PluginCacheCommand;
use EuBourne\LaravelPlugins\Console\PluginClearCommand;
use EuBourne\LaravelPlugins\Console\PluginCommand;
use EuBourne\LaravelPlugins\Console\PluginListCommand;
use EuBourne\LaravelPlugins\Contracts\PluginManager as PluginManagerContract;
use Illuminate\Foundation\Application;

class PluginServiceProvider extends BaseServiceProvider
{
    protected array $console = [
        PluginCacheCommand::class,
        PluginClearCommand::class,
        PluginCommand::class,
        PluginListCommand::class,
    ];

    public function register(): void
    {
        $this->app->bind('plugin.manager', PluginManagerContract::class);
        $this->app->singleton(PluginManagerContract::class, fn() => new PluginManager(config('plugins')));

        parent::register();

        $this->app->booted(function (Application $application) {
            $pluginManager = $application->make('plugin.manager');

            $this->registerRoutes($pluginManager);
            $this->registerChannels($pluginManager);
            $this->registerTranslations($pluginManager);
        });

        // Merge the configuration to allow overriding defaults
        $this->mergeConfigFrom(
            __DIR__ . '/../config/plugins.php', 'plugins'
        );
    }

    public function boot(PluginManagerContract $pluginManager): void
    {
        $pluginManager->registerProviders();

        $this->optimizeCommands();

        // Publish the configuration file
        $this->publishes([
            __DIR__ . '/../config/plugins.php' => config_path('plugins.php'),
        ], 'config');
    }

    protected function registerRoutes(PluginManagerContract $pluginManager): void
    {
        if (!$this->app->routesAreCached()) {
            $pluginManager->registerRoutes();
        }
    }

    protected function registerChannels(PluginManagerContract $pluginManager): void
    {
        $pluginManager->registerChannels();
    }

    protected function registerTranslations(PluginManagerContract $pluginManager): void
    {
        $pluginManager->registerTranslations();
    }

    protected function optimizeCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->optimizes(
                optimize: 'plugin:cache',
                clear: 'plugin:clear',
                key: 'plugins'
            );
        }
    }
}
