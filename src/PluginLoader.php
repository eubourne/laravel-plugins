<?php

namespace EuBourne\LaravelPlugins;

use EuBourne\LaravelPlugins\Contracts\PluginLoader as PluginLoaderContract;
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

        $plugins = collect();

        if (is_dir($path)) {
            $iterator = Finder::create()
                ->files()
                ->name('*' . $this->getSuffix() . '.php')
                ->sortByName()
                ->in($path);

            foreach ($iterator as $file) {
                $className = $this->classFromFile($file);

                if (class_exists($className)) {
                    /**
                     * @var Plugin $plugin
                     */
                    $plugin = new $className();

                    if ($plugin instanceof Plugin) {
                        $plugins->push($plugin->toArray());
                    }
                }
            }
        }

        return $plugins;
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
     * Returns plugin descriptor suffix
     *
     * @return string
     */
    protected function getSuffix(): string
    {
        return config('plugins.groups.' . $this->group . '.suffix')
            ?? config('plugins.suffix', 'Module');
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
}
