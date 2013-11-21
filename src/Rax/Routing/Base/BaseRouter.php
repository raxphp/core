<?php

namespace Rax\Routing\Base;

use ArrayAccess;
use Rax\Container\Container;
use Rax\Data\Data;
use Rax\Helper\Arr;
use Rax\Http\Response;
use Rax\Mvc\MatchedRoute;
use Rax\Routing\Route;
use Rax\Http\Request;
use Rax\Mvc\Service;

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
     * @param Data    $config
     */
    public function __construct(Container $container, Data $config)
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
        if (!$this->filterRoute($route)) {
            return false;
        }

        if (!preg_match($route->getRegex(), $request->getUri(), $matches)) {
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
     * Apply all the filters defined by the route.
     *
     * @param Route $route
     *
     * @return bool
     */
    public function filterRoute(Route $route)
    {
        foreach ($route->getFilters() as $name => $value) {
            $values = array('value' => $value, 'route' => $route);

            $filter = $this->container->get($name.'RouteFilter', $fqn = null, $values);

            if (!$this->container->call($filter, 'filter', $values)) {
                return false;
            }
        }

        return true;
    }
}
