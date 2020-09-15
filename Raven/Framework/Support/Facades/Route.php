<?php

namespace Raven\Support\Facades;

/**
 * @method static \Raven\Routing\Route fallback(\Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\Route get(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\Route post(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\Route put(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\Route delete(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\Route patch(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\Route options(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\Route any(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\Route match(array|string $methods, string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Raven\Routing\RouteRegistrar prefix(string  $prefix)
 * @method static \Raven\Routing\RouteRegistrar where(array  $where)
 * @method static \Raven\Routing\PendingResourceRegistration resource(string $name, string $controller, array $options = [])
 * @method static void resources(array $resources)
 * @method static void pattern(string $key, string $pattern)
 * @method static \Raven\Routing\PendingResourceRegistration apiResource(string $name, string $controller, array $options = [])
 * @method static void apiResources(array $resources, array $options = [])
 * @method static \Raven\Routing\RouteRegistrar middleware(array|string|null $middleware)
 * @method static \Raven\Routing\Route substituteBindings(\Raven\Support\Facades\Route $route)
 * @method static void substituteImplicitBindings(\Raven\Support\Facades\Route $route)
 * @method static \Raven\Routing\RouteRegistrar as(string $value)
 * @method static \Raven\Routing\RouteRegistrar domain(string $value)
 * @method static \Raven\Routing\RouteRegistrar name(string $value)
 * @method static \Raven\Routing\RouteRegistrar namespace(string $value)
 * @method static \Raven\Routing\Router|\Raven\Routing\RouteRegistrar group(\Closure|string|array $attributes, \Closure|string $routes)
 * @method static \Raven\Routing\Route redirect(string $uri, string $destination, int $status = 302)
 * @method static \Raven\Routing\Route permanentRedirect(string $uri, string $destination)
 * @method static \Raven\Routing\Route view(string $uri, string $view, array $data = [])
 * @method static void bind(string $key, string|callable $binder)
 * @method static void model(string $key, string $class, \Closure|null $callback = null)
 * @method static \Raven\Routing\Route current()
 * @method static string|null currentRouteName()
 * @method static string|null currentRouteAction()
 *
 * @see \Raven\Routing\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}