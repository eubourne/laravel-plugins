<?php

namespace EuBourne\LaravelPlugins\Contracts;

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
     * @return array
     */
    public function getPluginData(): array;

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
