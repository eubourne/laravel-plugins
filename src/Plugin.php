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
    protected array $routing = [
        'api' => ['filename' => 'api.php'],
        'web' => ['filename' => 'web.php'],
    ];

    /**
     * Default broadcasting configuration
     *
     * @var array|string[]
     */
    protected array $channels = [
        'channels.php'
    ];

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

    public function __construct(
        protected array $config = []
    )
    {
    }

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
        return Arr::get($this->config, 'group');
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
                        return collect(iterator_to_array($iterator))
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
        $routes = Arr::get($this->config, 'routes');

        return collect(is_array($routes) && count($routes)
            ? $routes
            : $this->routing);
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
            return collect(iterator_to_array($iterator))
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
        $channels = Arr::get($this->config, 'channels');

        return collect(is_array($channels) && count($channels)
            ? $channels
            : $this->channels);
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
            'translations' => $this->getTranslations(),
        ];
    }
}
