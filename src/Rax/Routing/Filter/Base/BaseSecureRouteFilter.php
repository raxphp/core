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
class BaseSecureRouteFilter
{
    /**
     * @throws Exception
     *
     * @param bool    $value
     * @param Request $request
     * @param Route   $route
     *
     * @return bool
     */
    public function filter($value, Request $request, Route $route)
    {
        if (!is_bool($value)) {
            throw new Exception('Route "%s", secure filter expects a boolean, got %s', array(
                $route->getName(),
                Php::getDataType($value),
            ));
        }

        return ($value === $request->isSecure());
    }
}
