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
}
