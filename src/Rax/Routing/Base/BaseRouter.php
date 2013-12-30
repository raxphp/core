<?php

namespace Rax\Routing\Base;

use ArrayAccess;
use Rax\Container\Container;
use Rax\Config\Config;
use Rax\Helper\Arr;
use Rax\Routing\MatchedRoute;
use Rax\Routing\Route;
use Rax\Http\Request;

/**
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
class BaseRouter
{
    /**
     * @var Route[]
     */
    protected $routes = array();

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     * @param Config    $config
     */
    public function __construct(Container $container, Config $config)
    {
        $this->container = $container;
        $this->load($config->get('routes'));
    }

    /**
     * @param array|ArrayAccess $routes
     *
     * @return $this
     */
    protected function load($routes)
    {
        foreach ($routes as $name => $route) {
            $this->routes[$name] = new Route(
                $name,
                $route['path'],
                $route['controller'],
                Arr::get($route, 'defaults', array()),
                Arr::get($route, 'rules', array()),
                Arr::get($route, 'filters', array())
            );
        }

        return $this;
    }

    /**
     * @param Route[] $routes
     *
     * @return $this
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * Returns the routes.
     *
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param Request $request
     *
     * @return MatchedRoute
     */
    public function match(Request $request)
    {
        foreach ($this->routes as $route) {
            if ($match = $this->matchRoute($route, $request)) {
                return $match;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     * @param Route   $route
     *
     * @return bool|MatchedRoute
     */
    public function matchRoute(Route $route, Request $request)
    {
        if (!$this->filter($route)) {
            return false;
        }

        if (!preg_match($route->getPattern(), $request->getUri(), $matches)) {
            return false;
        }

        array_shift($matches);

        $params = $route->getDefaults();

        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        return new MatchedRoute($route, $params);
    }

    /**
     * Checks if the route passes all the filters.
     *
     * @param Route $route
     *
     * @return bool
     */
    public function filter(Route $route)
    {
        foreach ($route->getFilters() as $name => $value) {
            $values = array(
                'value' => $value,
                'route' => $route,
            );

            $filter = $this->container->getById($name.'RouteFilter', $values);

            if (!$this->container->call($filter, 'filter', $values)) {
                return false;
            }
        }

        return true;
    }

}
