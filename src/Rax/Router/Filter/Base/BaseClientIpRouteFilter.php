<?php

namespace Rax\Router\Filter\Base;

use Rax\Http\Request;

/**
 * Client IP route filter.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseClientIpRouteFilter
{
    /**
     * Filters the route by checking the client's IP address.
     *
     * @param string  $value
     * @param Request $request
     *
     * @return bool
     */
    public function filter($value, Request $request)
    {
        return preg_match('/^'.$value.'$/', $request->getClientIp());
    }
}
