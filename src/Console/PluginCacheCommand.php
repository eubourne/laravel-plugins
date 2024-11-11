<?php

namespace EuBourne\LaravelPlugins\Console;

class PluginCacheCommand extends PluginCommandAbstract
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'plugin:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cache file for faster plugins loading';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->callSilent('plugin:clear');

        $this->pluginManager->cache();

        if (!$this->pluginManager->count()) {
            $this->components->warn("Your application doesn't have any plugins.");
            return;
        }

        $this->components->info('Plugins cached successfully.');
    }
}
