<?php

namespace Rax\Observer\Base;

use Rax\Http\Request;
use Rax\Routing\Router;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseRequestObserver
{
    /**
     * @param Router  $router
     * @param Request $request
     *
     * @return $this
     */
    public function trigger(Router $router, Request $request)
    {
        $matchedRoute = $router->match($request);

        return $this;

//        if (!) {
//            // throw 404
//            throw new Exception('todo throw 404');
//        }
//
//        $this->request->setRouteMatch($match);
//
//        $response = new Response();
//
//        $this->service->routeMatch = $match;
//        $this->service->response = $response;
//
//        $controller = $this->service->build($match->getControllerClassName());
//
//        $service = $this->service;
//
//        if (method_exists($controller, '__before')) {
//            $service->call($controller, '__before');
//        }
//
//        if (method_exists($controller, 'before')) {
//            $service->call($controller, 'before');
//        }
//
//        $service->call($controller, $match->getActionMethodName(), $match->getParams());
//
//        if (method_exists($controller, 'after')) {
//            $service->call($controller, 'after');
//        }
//
//        if (method_exists($controller, '__after')) {
//            $service->call($controller, '__after');
//        }
//
//        return $response;
    }
}
