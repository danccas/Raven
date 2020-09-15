<?php

namespace Raven\Support\Facades;

/**
 * @method static \Raven\Redis\Connections\Connection connection(string $name = null)
 * @method static \Raven\Redis\Limiters\ConcurrencyLimiterBuilder funnel(string $name)
 * @method static \Raven\Redis\Limiters\DurationLimiterBuilder throttle(string $name)
 *
 * @see \Raven\Redis\RedisManager
 * @see \Raven\Contracts\Redis\Factory
 */
class Redis extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'redis';
    }
}
