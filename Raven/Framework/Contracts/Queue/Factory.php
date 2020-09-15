<?php

namespace Raven\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string|null  $name
     * @return \Raven\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
