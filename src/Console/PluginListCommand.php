<?php

namespace EuBourne\LaravelPlugins\Console;

use EuBourne\LaravelPlugins\Contracts\PluginManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PluginListCommand extends PluginCommandAbstract
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'plugin:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered plugins';

    protected Collection $plugins;

    public function __construct(PluginManager $manager)
    {
        parent::__construct($manager);

        $this->plugins = collect($this->pluginManager->getPluginData());
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->pluginsExist()) {
            return;
        }

        $this->displayAllPlugins();
    }

    protected function displayAllPlugins(): void
    {
        $this->newLine();
        $this->displayCacheStatus();
        $this->newLine();

        $this->plugins->each(function (array $plugin, string $key) {
            $this->components->twoColumnDetail($key, $this->format(Arr::get($plugin, 'path'), static::PLUGIN_PATH));

            if ($this->output->isVerbose()) {
                $this->displayProviders(Arr::get($plugin, 'providers', []));
                $this->displayRoutes(Arr::get($plugin, 'routes', []));
            }
        });

        $this->newLine();
    }

    protected function displayProviders(array $providers): void
    {
        $this->line('    ' . $this->format(text: 'Providers:', color: static::MUTED));

        foreach ($providers as $provider) {
            $text = '    ' . $this->format(text: '⇂ ' . $provider, color: static::PROVIDERS);
            $this->line($text);
        }
    }

    protected function displayRoutes(array $routes): void
    {
        $this->line('    ' . $this->format(text: 'Routes:', color: static::MUTED));

        foreach ($routes as $route) {
            $filename = pathinfo($route['path'] ?? '', PATHINFO_BASENAME);
            $middleware = $route['middleware'] ?? null;

            $text = '    ' . $this->format(text: '⇂ ' . $filename, color: static::ROUTES);

            if ($middleware) {
                $middleware = implode(', ', Arr::wrap($middleware));

                $space = str_repeat(' ', static::MIDDLEWARE_OFFSET_LEFT - mb_strlen($text));

                $text .= $space . $this->format('middleware: ', static::MUTED)
                    . $this->format($middleware, static::MIDDLEWARE);
            }

            $this->line($text);
        }
    }
}
