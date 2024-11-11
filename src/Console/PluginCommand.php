<?php

namespace EuBourne\LaravelPlugins\Console;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PluginCommand extends PluginCommandAbstract
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'plugin {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display plugin details';

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

        $key = $this->argument('key');
        $plugin = $this->getPlugin($key);

        $plugin
            ? $this->displayPlugin($plugin)
            : $this->displayPluginNotFound($key);

        $this->newLine();
    }

    protected function getPlugin(string $key): ?array
    {
        return Arr::get($this->pluginManager->getPluginData(), $key);
    }

    protected function displayPluginNotFound(string $notFoundKey): void
    {
        $this->components->error('Plugin \'' . $notFoundKey . '\' not found');

        // Find possible
        $possible = collect($this->pluginManager->getPluginData())
            ->filter(fn(array $plugin, string $key) => Str::contains($key, $notFoundKey));

        if ($possible->count()) {
            $this->line('Did you mean one of the following?');
            $possible->each(fn(array $plugin, string $key) => $this->line('  ' . $this->format($key, static::COLOR_GREEN)));
        }
    }

    protected function displayPlugin(array $plugin): void
    {
        $this->newLine();
        $this->displayCacheStatus();
        $this->newLine();

        $this->components->twoColumnDetail($this->format('Plugin', static::TITLE));

        $data = [
            'Group' => Arr::get($plugin, 'group'),
            'Key' => Arr::get($plugin, 'key'),
            'Directory' => Arr::get($plugin, 'path'),
            'Namespace' => Arr::get($plugin, 'namespace'),
            'Descriptor class' => Arr::get($plugin, 'className'),
        ];

        foreach ($data as $key => $value) {
            $this->components->twoColumnDetail($key, $value);
        }

        $this->displayProviders(Arr::get($plugin, 'providers'));
        $this->displayRoutes(Arr::get($plugin, 'routes'));
        $this->displayChannels(Arr::get($plugin, 'channels', []));
        $this->displayTranslations(Arr::get($plugin, 'translations'));
    }

    protected function displayProviders(array $providers): void
    {
        if (count($providers) > 1) {
            $this->newLine();
            $this->components->twoColumnDetail($this->format('Service providers', static::TITLE));

            foreach ($providers as $provider) {
                $this->components->twoColumnDetail($this->format($provider, static::PROVIDERS));
            }
        } elseif (count($providers)) {
            $this->components->twoColumnDetail('Service provider', $this->format($providers[0], static::PROVIDERS));
        }
    }

    protected function displayRoutes(array $routes): void
    {
        if (!count($routes)) {
            return;
        }

        $this->newLine();
        $this->components->twoColumnDetail($this->format('Routes', static::TITLE));

        foreach ($routes as $route) {
            $filename = pathinfo($route['path'] ?? '', PATHINFO_BASENAME);
            $middleware = $route['middleware'] ?? null;

            if ($middleware) {
                $middleware = implode(', ', Arr::wrap($middleware));
                $middleware = $this->format($middleware, static::MIDDLEWARE);
            }

            $this->components->twoColumnDetail($this->format($filename, static::ROUTES), $middleware);
        }
    }

    protected function displayChannels(array $channels): void
    {
        if (count($channels)) {
            $this->newLine();
            $this->components->twoColumnDetail($this->format('Broadcasting', static::TITLE));

            foreach ($channels as $channel) {
                $this->components->twoColumnDetail($this->format($channel, static::CHANNELS));
            }
        }
    }

    protected function displayTranslations(?string $translations): void
    {
        if ($translations) {
            $this->newLine();
            $this->components->twoColumnDetail($this->format('Translations', static::TITLE));
            $this->components->twoColumnDetail($this->format($translations, static::TRANSLATIONS));
        }
    }
}
