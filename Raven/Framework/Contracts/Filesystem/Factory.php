<?php

namespace Raven\Contracts\Filesystem;

interface Factory
{
    /**
     * Get a filesystem implementation.
     *
     * @param  string|null  $name
     * @return \Raven\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null);
}
