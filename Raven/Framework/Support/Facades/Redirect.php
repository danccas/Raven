<?php

namespace Raven\Support\Facades;

/**
 * @method static \Raven\Http\RedirectResponse home(int $status = 302)
 * @method static \Raven\Http\RedirectResponse back(int $status = 302, array $headers = [], $fallback = false)
 * @method static \Raven\Http\RedirectResponse refresh(int $status = 302, array $headers = [])
 * @method static \Raven\Http\RedirectResponse guest(string $path, int $status = 302, array $headers = [], bool $secure = null)
 * @method static \Raven\Http\RedirectResponse intended(string $default = '/', int $status = 302, array $headers = [], bool $secure = null)
 * @method static \Raven\Http\RedirectResponse to(string $path, int $status = 302, array $headers = [], bool $secure = null)
 * @method static \Raven\Http\RedirectResponse away(string $path, int $status = 302, array $headers = [])
 * @method static \Raven\Http\RedirectResponse secure(string $path, int $status = 302, array $headers = [])
 * @method static \Raven\Http\RedirectResponse route(string $route, array $parameters = [], int $status = 302, array $headers = [])
 * @method static \Raven\Http\RedirectResponse action(string $action, array $parameters = [], int $status = 302, array $headers = [])
 * @method static \Raven\Routing\UrlGenerator getUrlGenerator()
 * @method static void setSession(\Raven\Session\Store $session)
 *
 * @see \Raven\Routing\Redirector
 */
class Redirect extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'redirect';
    }
}
