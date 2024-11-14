<?php

namespace EuBourne\LaravelPlugins;

use EuBourne\LaravelPlugins\Contracts\PluginLoader as PluginLoaderContract;
use EuBourne\LaravelPlugins\Contracts\PluginManager as PluginManagerContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PluginLoader implements PluginLoaderContract
{
    public function __construct(
        protected string $group,
        protected array  $config
    )
    {
    }

    /**
     * Return a collection of discovered plugins by scanning a group directory
     *
     * @return Collection
     */
    public function discoverPlugins(): Collection
    {
        $path = $this->getScanPath();

        $files = collect();

        if (is_dir($path)) {
            $iterator = Finder::create()
                ->files()
                ->name('*Module.php')
                ->in($path);

            foreach ($iterator as $file) {
                $className = $this->classFromFile($file);

                if (class_exists($className)) {
                    /**
                     * @var Plugin $plugin
                     */
                    $plugin = new $className($this->getPluginConfig());

                    if ($plugin instanceof Plugin) {
                        $files->push($plugin->toArray());
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Returns absolute path to a directory to scan
     *
     * @return string|null
     */
    protected function getScanPath(): ?string
    {
        $path = $this->config['path'] ?? null;

        return $path ? base_path($path) : null;
    }

    /**
     * Extract the class name from the given file path.
     *
     * @param SplFileInfo $file
     * @return string
     */
    protected function classFromFile(SplFileInfo $file): string
    {
        $contents = $file->getContents();
        $namespace = $class = null;

        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = $matches[1];
        }
        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $class = $matches[1];
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }

    /**
     * Get configuration for a plugin instance
     *
     * @return array
     */
    protected function getPluginConfig(): array
    {
        return [
            'group' => $this->group,
            'routes' => $this->getRouting(),
        ];
    }

    /**
     * Get routing configuration specific to a plugin group
     *
     * @return array
     */
    public function getRouting(): array
    {
        /**
         * @var PluginManagerContract $manager
         */
        $manager = app('plugin.manager');
        $globalRoutes = $manager->getRouting();

        return array_merge_recursive(
            $globalRoutes,
            Arr::get($this->config, 'routes') ?? []
        );
    }

    /**
     * Get broadcasting configuration specific to a plugin group
     *
     * @return array
     */
    public function getBroadcasting(): array
    {
        /**
         * @var PluginManagerContract $manager
         */
        $manager = app('plugin.manager');
        $globalRoutes = $manager->getBroadcasting();

        return array_merge_recursive(
            $globalRoutes,
            Arr::get($this->config, 'channels') ?? []
        );
    }
}
