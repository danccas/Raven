<?php

namespace Raven\Support\Facades;

use Raven\Contracts\Bus\Dispatcher as BusDispatcherContract;
use Raven\Support\Testing\Fakes\BusFake;

/**
 * @method static mixed dispatch($command)
 * @method static mixed dispatchNow($command, $handler = null)
 * @method static bool hasCommandHandler($command)
 * @method static bool|mixed getCommandHandler($command)
 * @method static \Raven\Contracts\Bus\Dispatcher pipeThrough(array $pipes)
 * @method static \Raven\Contracts\Bus\Dispatcher map(array $map)
 * @method static void assertDispatched(string $command, callable|int $callback = null)
 * @method static void assertDispatchedTimes(string $command, int $times = 1)
 * @method static void assertNotDispatched(string $command, callable|int $callback = null)
 *
 * @see \Raven\Contracts\Bus\Dispatcher
 */
class Bus extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param  array|string  $jobsToFake
     * @return \Raven\Support\Testing\Fakes\BusFake
     */
    public static function fake($jobsToFake = [])
    {
        static::swap($fake = new BusFake(static::getFacadeRoot(), $jobsToFake));

        return $fake;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BusDispatcherContract::class;
    }
}
