<?php

namespace Rax\Router\Base;

use ArrayAccess;
use Rax\Container\Container;
use Rax\Config\Config;
use Rax\Helper\Arr;
use Rax\Router\MatchedRoute;
use Rax\Router\Route;
use Rax\Http\Request;

/**
 * Router matches a route from a given request.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
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
        $this->load($config->get('route'));
    }

    /**
     * Loads a route object array from config.
     *
     *     $router->load($config->get('route'));
     *
     * @param array|ArrayAccess $routes
     *
     * @return $this
     */
    public function load($routes)
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
     * Sets the routes.
     *
     *     $router->setRoutes(array(
     *         new Route('blog', '/blog', 'Blog:index'),
     *     ));
     *
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
     * Gets the routes.
     *
     *     $routes = $router->getRoutes();
     *
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Matches a route given a request.
     *
     * NOTE: The first match is returned, and further processing is stopped.
     *
     *     $matchedRoute = $router->match($request);
     *
     * @param Request $request
     *
     * @return MatchedRoute|bool
     */
    public function match(Request $request)
    {
        foreach ($this->routes as $route) {
            if ($matchedRoute = $this->matchRoute($route, $request)) {
                return $matchedRoute;
            }
        }

        return false;
    }

    /**
     * Checks if a route matches a request, if so returns a MatchedRoute object.
     *
     *     $matchedRoute = $router->matchRoute($route, $request);
     *
     * @param Request $request
     * @param Route   $route
     *
     * @return MatchedRoute|bool
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

        $placeHolders = $route->getDefaults();

        foreach ($matches as $placeHolder => $value) {
            if (!is_int($placeHolder)) {
                $placeHolders[$placeHolder] = $value;
            }
        }

        return new MatchedRoute($route, $placeHolders);
    }

    /**
     * Filters a route.
     *
     * Checks if the route passes all its filters given the environment.
     *
     *     if ($router->filter($route)) {
     *
     * @param Route $route
     *
     * @return bool
     */
    public function filter(Route $route)
    {
        foreach ($route->getFilters() as $filter => $value) {
            if (!$this->container->call($filter.'RouteFilter', 'filter', array('value' => $value, $route))) {
                return false;
            }
        }

        return true;
    }
}
