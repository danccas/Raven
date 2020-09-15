<?php

namespace Raven\Support\Facades;

/**
 * @method static \Raven\Contracts\Auth\Guard|\Raven\Contracts\Auth\StatefulGuard guard(string|null $name = null)
 * @method static void shouldUse(string $name);
 * @method static bool check()
 * @method static bool guest()
 * @method static \Raven\Contracts\Auth\Authenticatable|null user()
 * @method static int|null id()
 * @method static bool validate(array $credentials = [])
 * @method static void setUser(\Raven\Contracts\Auth\Authenticatable $user)
 * @method static bool attempt(array $credentials = [], bool $remember = false)
 * @method static bool once(array $credentials = [])
 * @method static void login(\Raven\Contracts\Auth\Authenticatable $user, bool $remember = false)
 * @method static \Raven\Contracts\Auth\Authenticatable loginUsingId(mixed $id, bool $remember = false)
 * @method static bool onceUsingId(mixed $id)
 * @method static bool viaRemember()
 * @method static void logout()
 * @method static \Symfony\Component\HttpFoundation\Response|null onceBasic(string $field = 'email',array $extraConditions = [])
 * @method static bool|null logoutOtherDevices(string $password, string $attribute = 'password')
 * @method static \Raven\Contracts\Auth\UserProvider|null createUserProvider(string $provider = null)
 * @method static \Raven\Auth\AuthManager extend(string $driver, \Closure $callback)
 * @method static \Raven\Auth\AuthManager provider(string $name, \Closure $callback)
 *
 * @see \Raven\Auth\AuthManager
 * @see \Raven\Contracts\Auth\Factory
 * @see \Raven\Contracts\Auth\Guard
 * @see \Raven\Contracts\Auth\StatefulGuard
 */
class Auth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth';
    }

    /**
     * Register the typical authentication routes for an application.
     *
     * @param  array  $options
     * @return void
     */
    public static function routes(array $options = [])
    {
        static::$app->make('router')->auth($options);
    }
}
