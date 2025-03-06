<?php

namespace EuBourne\LaravelPlugins;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

abstract class BaseServiceProvider extends ServiceProvider
{
    /**
     * List of providers to register
     *
     * @var array<string>
     */
    protected array $providers = [];

    /**
     * List of console commands to register
     *
     * @var array<string>
     */
    protected array $console = [];

    /**
     * The subscriber classes to register.
     *
     * @var array<string>
     */
    protected array $subscribe = [];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerSubscribers();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerProviders();
        $this->registerCommands();
        $this->runSchedule();
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

    /**
     * Register event subscribers
     *
     * @return void
     */
    protected function registerSubscribers(): void
    {
        foreach ($this->subscribe as $subscriber) {
            Event::subscribe($subscriber);
        }
    }

    /**
     * Handle the schedule
     *
     * @return void
     */
    protected function runSchedule(): void
    {
        if (method_exists($this, 'schedule')) {
            Artisan::starting(fn() => $this->{'schedule'}($this->app->make(Schedule::class)));
        }
    }
}
