<?php

namespace EuBourne\LaravelPlugins\Contracts;

use EuBourne\LaravelPlugins\Plugin;

interface PluginManager
{
    /**
     * Load plugin service providers
     *
     * @return self
     */
    public function registerProviders(): self;

    /**
     * Register plugin routes
     *
     * @return self
     */
    public function registerRoutes(): self;

    /**
     * Register plugin broadcasting authorization rules
     *
     * @return self
     */
    public function registerChannels(): self;

    /**
     * Register translations
     *
     * @return self
     */
    public function registerTranslations(): self;

    /**
     * Returns a list of discovered plugins with all the metadata
     *
     * @param string|null $fullKey
     * @return array
     */
    public function getPluginData(string $fullKey = null): array;

    /**
     * Return a list of discovered plugin keys.
     *
     * @return array
     */
    public function getKeys(): array;

    /**
     * Retrieve plugin data field from a specified plugin using dot notation.
     *
     * @param string $fullKey
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getFromPlugin(string $fullKey, string $key, mixed $default = null): mixed;

    /**
     * Return instance of a plugin descriptor class
     *
     * @param string $fullKey
     * @return Plugin
     */
    public function getPlugin(string $fullKey): Plugin;

    /**
     * Return number of registered plugins
     *
     * @return int
     */
    public function count(): int;

    /**
     * Cache plugin data
     *
     * @return self
     */
    public function cache(): self;

    /**
     * Clear cached plugin data
     *
     * @return self
     */
    public function clear(): self;

    /**
     * Determine if the application plugins are cached.
     *
     * @return bool
     */
    public function pluginsAreCached(): bool;
}
