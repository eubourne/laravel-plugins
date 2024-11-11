<?php

namespace EuBourne\LaravelPlugins\Contracts;

use Illuminate\Support\Collection;

interface PluginLoader
{
    /**
     * Return a collection of discovered plugins by scanning a group directory
     *
     * @return Collection
     */
    public function discoverPlugins(): Collection;

    /**
     * Get routing configuration specific to a plugin group
     *
     * @return array
     */
    public function getRouting(): array;

    /**
     * Get broadcasting configuration specific to a plugin group
     *
     * @return array
     */
    public function getBroadcasting(): array;
}
