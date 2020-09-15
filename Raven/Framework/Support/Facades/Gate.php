<?php

namespace Raven\Support\Facades;

use Raven\Contracts\Auth\Access\Gate as GateContract;

/**
 * @method static bool has(string $ability)
 * @method static \Raven\Contracts\Auth\Access\Gate define(string $ability, callable|string $callback)
 * @method static \Raven\Contracts\Auth\Access\Gate policy(string $class, string $policy)
 * @method static \Raven\Contracts\Auth\Access\Gate before(callable $callback)
 * @method static \Raven\Contracts\Auth\Access\Gate after(callable $callback)
 * @method static bool allows(string $ability, array|mixed $arguments = [])
 * @method static bool denies(string $ability, array|mixed $arguments = [])
 * @method static bool check(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool any(iterable|string $abilities, array|mixed $arguments = [])
 * @method static \Raven\Auth\Access\Response authorize(string $ability, array|mixed $arguments = [])
 * @method static mixed raw(string $ability, array|mixed $arguments = [])
 * @method static mixed getPolicyFor(object|string $class)
 * @method static \Raven\Contracts\Auth\Access\Gate forUser(\Raven\Contracts\Auth\Authenticatable|mixed $user)
 * @method static array abilities()
 * @method static \Raven\Auth\Access\Response inspect(string $ability, array|mixed $arguments = [])
 * @method static \Raven\Auth\Access\Gate guessPolicyNamesUsing(callable $callback)
 *
 * @see \Raven\Contracts\Auth\Access\Gate
 */
class Gate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return GateContract::class;
    }
}
