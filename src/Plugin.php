<?php

namespace EuBourne\LaravelPlugins;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Plugin
{
    /**
     * Plugin key
     *
     * @var string
     */
    protected string $key;

    /**
     * Default routing configuration
     *
     * @var array|array[]
     */
    protected array $routing;

    /**
     * Default broadcasting configuration
     *
     * @var array|string[]
     */
    protected array $channels;

    /**
     * List of service providers to register
     *
     * @var array
     */
    protected array $providers = [];

    /**
     * Default service provider name
     *
     * @var string
     */
    protected string $defaultServiceProviderName = 'Providers\\ServiceProvider';

    public function getKey(): string
    {
        if (isset($this->key) && $this->key) {
            return $this->key;
        }

        $pieces = explode('\\', get_called_class());
        array_shift($pieces);
        array_pop($pieces);

        return strtolower(implode('.', $pieces));
    }

    public function getGroup(): ?string
    {
        if (isset($this->group) && $this->group) {
            return $this->group;
        }

        $pieces = explode('\\', get_called_class());
        return strtolower(array_shift($pieces));
    }

    public function getNamespace(): string
    {
        $class = new \ReflectionClass(get_called_class());
        return $class->getNamespaceName();
    }

    public function getPath(string|array $path = null): string
    {
        $class = new \ReflectionClass(get_called_class());
        $pluginPath = pathinfo($class->getFileName(), PATHINFO_DIRNAME);

        return $path
            ? implode(DIRECTORY_SEPARATOR, array_merge([$pluginPath], Arr::wrap($path)))
            : $pluginPath;
    }

    public function getRouteFiles(): array
    {
        // Get route directory path
        $dir = $this->getPath('Routes');

        if (is_dir($dir)) {
            // Process each routing group
            return $this->getRouting()
                ->flatMap(function (array $routingGroup) use ($dir) {
                    if ($filenamePattern = Arr::get($routingGroup, 'filename')) {
                        // Look up route files matching a given filename pattern
                        $iterator = Finder::create()
                            ->files()
                            ->name($filenamePattern)
                            ->in($dir)
                            ->contains('Route::')
                            ->sortByName();

                        // Format routing data output
                        return (new Collection(iterator_to_array($iterator)))
                            ->map(fn(SplFileInfo $file) => array_merge(
                                Arr::except($routingGroup, ['filename']),
                                ['path' => $file->getPathname()]
                            ))
                            ->values()
                            ->all();
                    }

                    // If failed to get route filename pattern skip route group processing
                    return null;
                })
                ->filter()
                ->all();
        }

        return [];
    }

    /**
     * Get routing rules for the plugin
     *
     * @return Collection
     */
    protected function getRouting(): Collection
    {
        if (isset($this->routing) && count($this->routing)) {
            $routing = $this->routing;
        } else {
            $routing = config('plugins.groups.' . $this->getGroup() . '.routes')
                ?? config('plugins.routes');
        }

        return new Collection(is_array($routing) && count($routing)
            ? $routing
            : [
                'api' => ['filename' => 'api.php'],
                'web' => ['filename' => 'web.php'],
            ]);
    }

    /**
     * Get a list of broadcast configuration files
     *
     * @return array
     */
    public function getChannelFiles(): array
    {
        // Get route directory path
        $dir = $this->getPath('Routes');

        if (is_dir($dir)) {
            // Look up route files matching a given filename pattern
            $iterator = Finder::create()
                ->files()
                ->name($this->getBroadcasting()->all())
                ->in($dir)
                ->contains('Broadcast::')
                ->sortByName();

            // Format output
            return (new Collection(iterator_to_array($iterator)))
                ->map(fn(SplFileInfo $file) => $file->getPathname())
                ->values()
                ->all();
        }

        return [];
    }

    /**
     * Get broadcasting rules for the plugin
     *
     * @return Collection
     */
    protected function getBroadcasting(): Collection
    {
        if (isset($this->channels) && count($this->channels)) {
            $channels = $this->channels;
        } else {
            $channels = config('plugins.groups.' . $this->getGroup() . '.channels')
                ?? config('plugins.channels');
        }

        return new Collection(is_array($channels) && count($channels)
            ? $channels
            : ['channels.php']
        );
    }

    /**
     * Get a list of plugin service providers
     *
     * @return array
     */
    public function getProviders(): array
    {
        return array_filter(count($this->providers)
            ? $this->providers
            : Arr::wrap($this->getNamespace() . '\\' . $this->defaultServiceProviderName),
            function (string $className) {
                try {
                    $reflection = new ReflectionClass($className);
                    return $reflection->isSubclassOf(ServiceProvider::class);
                } catch (ReflectionException) {
                }

                return false;
            }
        );
    }

    /**
     * Get a list of plugin listener paths
     *
     * @return string[]|null
     */
    public function getListeners(): array|null
    {
        $default = $this->getPath('Listeners');

        return property_exists($this, 'listeners')
            ? $this->{'listeners'}
            : (file_exists($default) ? [$default] : null);
    }

    /**
     * Get path to a directory with plugin translation files
     *
     * @return string|null
     */
    public function getTranslations(): ?string
    {
        // Get route directory path
        $dir = $this->getPath('Lang');

        if (is_dir($dir)) {
            // Look up route files matching a given filename pattern
            $hasTranslations = Finder::create()
                ->files()
                ->name('*.php')
                ->in($dir)
                ->hasResults();

            if ($hasTranslations) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * Return complete plugin data
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'group' => $this->getGroup(),
            'namespace' => $this->getNamespace(),
            'className' => get_called_class(),
            'path' => $this->getPath(),
            'providers' => $this->getProviders(),
            'routes' => $this->getRouteFiles(),
            'channels' => $this->getChannelFiles(),
            'listeners' => $this->getListeners(),
            'translations' => $this->getTranslations(),
        ];
    }
}
