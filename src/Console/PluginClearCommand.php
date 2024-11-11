<?php

namespace EuBourne\LaravelPlugins\Console;

class PluginClearCommand extends PluginCommandAbstract
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the plugin cache file';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->pluginManager->clear();

        $this->components->info('Plugin cache cleared successfully.');
    }
}
