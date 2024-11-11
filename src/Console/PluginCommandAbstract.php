<?php

namespace EuBourne\LaravelPlugins\Console;

use EuBourne\LaravelPlugins\Contracts\PluginManager;
use EuBourne\LaravelPlugins\Traits\SupportsFormatting;
use Illuminate\Console\Command;

abstract class PluginCommandAbstract extends Command
{
    use SupportsFormatting;

    const int MIDDLEWARE_OFFSET_LEFT = 50;

    /**
     * Define output color scheme
     */
    const string TITLE = PluginCommandAbstract::COLOR_GREEN;
    const string MUTED = PluginCommandAbstract::COLOR_GRAY;
    const string PLUGIN_PATH = PluginCommandAbstract::COLOR_VIOLET;
    const string PROVIDERS = PluginCommandAbstract::COLOR_MAGENTA;
    const string ROUTES = PluginCommandAbstract::COLOR_BLUE;
    const string MIDDLEWARE = PluginCommandAbstract::COLOR_CYAN;
    const string CHANNELS = PluginCommandAbstract::COLOR_RED;
    const string TRANSLATIONS = PluginCommandAbstract::COLOR_YELLOW;

    protected PluginManager $pluginManager;

    public function __construct(PluginManager $manager)
    {
        parent::__construct();

        $this->pluginManager = $manager;
    }

    protected function pluginsExist(): bool
    {
        if (!$this->pluginManager->count()) {
            $this->components->error("Your application doesn't have any plugins.");
            return false;
        }

        return true;
    }

    protected function displayCacheStatus(): void
    {
        $this->components->twoColumnDetail('Plugins cache', $this->pluginManager->pluginsAreCached()
            ? $this->format(text: 'CACHED', color: static::COLOR_GREEN, bold: true)
            : $this->format(text: 'NOT CACHED', color: static::COLOR_YELLOW, bold: true)
        );
    }
}
