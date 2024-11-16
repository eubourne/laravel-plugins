<?php

namespace EuBourne\LaravelPlugins\Console;

use EuBourne\LaravelPlugins\PluginServiceProvider;

class PluginInstallCommand extends PluginCommandAbstract
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:install
                            {--f|force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Laravel Plugins resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $params = [
            '--provider' => PluginServiceProvider::class,
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        $this->components->info('Laravel Plugins scaffolding installed successfully.');
    }
}
