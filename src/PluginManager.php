<?php

namespace EuBourne\LaravelPlugins;

use EuBourne\LaravelPlugins\Contracts\PluginManager as PluginManagerContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Psr\SimpleCache\InvalidArgumentException;

class PluginManager implements PluginManagerContract
{
    const string CACHE_KEY = 'laravel-plugins';

    protected Application $app;
    protected Repository $cache;

    public function __construct(protected array $config)
    {
        $this->app = app();
        $this->cache = Cache::store(Arr::get($this->config, 'cache_store'));
    }

    /**
     * Register plugin service providers
     *
     * @return $this
     */
    public function registerProviders(): self
    {
        collect($this->getPluginData())
            ->pluck('providers')
            ->flatten()
            ->filter()
            ->values()
            ->each(function (string $className) {
                if (class_exists($className)) {
                    app()->register($className);
                }
            });

        return $this;
    }

    /**
     * Register plugin routes
     *
     * @return $this
     */
    public function registerRoutes(): self
    {
        collect($this->getPluginData())
            ->pluck('routes')
            ->flatten(1)
            ->filter()
            ->values()
            ->each(function (array $routeConfig) {
                $path = Arr::get($routeConfig, 'path');

                if ($path && file_exists($path)) {
                    Route::middleware(Arr::get($routeConfig, 'middleware'))
                        ->group($path);
                }
            });

        return $this;
    }

    /**
     * Register broadcasting authorizations
     *
     * @return $this
     */
    public function registerChannels(): self
    {
        collect($this->getPluginData())
            ->pluck('channels')
            ->flatten()
            ->filter()
            ->values()
            ->each(function (string $path) {
                if ($path && file_exists($path)) {
                    require_once $path;
                }
            });

        return $this;
    }

    /**
     * Register plugin translations
     *
     * @return $this
     */
    public function registerTranslations(): self
    {
        $plugins = collect($this->getPluginData())
            ->map(fn(array $plugin) => Arr::get($plugin, 'translations'))
            ->filter();

        if ($plugins->count()) {
            $this->callAfterResolving(
                name: 'translator',
                callback: function (Translator $translator) use ($plugins) {
                    $plugins->each(fn(string $path, string $namespace) => $translator->addNamespace($namespace, $path));
                }
            );
        }

        return $this;
    }

    /**
     * Set up an after resolving listener, or fire immediately if already resolved.
     *
     * @param string $name
     * @param callable $callback
     * @return void
     */
    protected function callAfterResolving(string $name, callable $callback): void
    {
        $this->app->afterResolving($name, $callback);

        if ($this->app->resolved($name)) {
            try {
                $callback($this->app->make($name), $this->app);
            } catch (BindingResolutionException) {
            }
        }
    }

    /**
     * Returns a list of discovered plugins with all the metadata
     *
     * @return array
     */
    public function getPluginData(): array
    {
        try {
            $cached = $this->cache->get(static::CACHE_KEY);

            if (is_array($cached) && count($cached)) {
                return $cached;
            }
        } catch (InvalidArgumentException $e) {
        }

        return $this->discoverPlugins()->all();
    }

    /**
     * Scan plugin directories and discover plugins
     *
     * @return Collection
     */
    protected function discoverPlugins(): Collection
    {
        return collect($this->config['groups'] ?? [])
            ->flatMap(function (array $config, string $group) {
                $loader = new PluginLoader($group, $config);
                return $loader
                    ->discoverPlugins()
                    ->keyBy(fn(array $plugin) => $group . '.' . $plugin['key']);
            });
    }

    /**
     * Return number of registered plugins
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->getPluginData());
    }

    /**
     * Cache plugin data
     *
     * @return $this
     */
    public function cache(): self
    {
        $this->cache->forever(static::CACHE_KEY, $this->discoverPlugins()->all());

        return $this;
    }

    /**
     * Clear plugin data
     *
     * @return self
     */
    public function clear(): self
    {
        $this->cache->forget(static::CACHE_KEY);

        return $this;
    }

    /**
     * Determine if the application plugins are cached.
     *
     * @return bool
     */
    public function pluginsAreCached(): bool
    {
        try {
            return $this->cache->has(static::CACHE_KEY);
        } catch (InvalidArgumentException) {
        }

        return false;
    }

    /**
     * Get global plugin route definition rules
     *
     * @return array
     */
    public function getRouting(): array
    {
        return Arr::get($this->config, 'routes', []);
    }

    /**
     * Get global plugin broadcasting definition rules
     *
     * @return array
     */
    public function getBroadcasting(): array
    {
        return Arr::get($this->config, 'channels', []);
    }
}
