<?php

namespace Rax\Routing\Filter\Base;

use Rax\Exception\Exception;
use Rax\Helper\Php;
use Rax\Http\Request;
use Rax\Routing\Route;

/**
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
class BaseServerIpRouteFilter
{
    /**
     * @throws Exception
     *
     * @param string  $value
     * @param Request $request
     * @param Route   $route
     *
     * @return bool
     */
    public function filter($value, Request $request, Route $route)
    {
        if (!is_string($value)) {
            throw new Exception('Route "%s", serverIp filter expects a string, got %s', array(
                $route->getName(),
                Php::getDataType($value),
            ));
        }

        if (!strlen($value)) {
            throw new Exception('Route "%s", serverIp filter is missing a value', array(
                $route->getName(),
            ));
        }

        return preg_match('#^'.$value.'$#', $request->getServerIp());
    }
}
