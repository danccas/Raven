<?php

namespace Raven\Contracts\Cache;

interface Factory
{
    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $name
     * @return \Raven\Contracts\Cache\Repository
     */
    public function store($name = null);
}
