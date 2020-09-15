<?php

namespace Raven\Contracts\Redis;

interface Factory
{
    /**
     * Get a Redis connection by name.
     *
     * @param  string|null  $name
     * @return \Raven\Redis\Connections\Connection
     */
    public function connection($name = null);
}
