<?php

namespace Rax\Routing\Filter\Base;

use Rax\Http\Request;

/**
 * Server IP route filter.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseServerIpRouteFilter
{
    /**
     * Filters a route by checking the server's IP address.
     *
     * @param string  $value
     * @param Request $request
     *
     * @return bool
     */
    public function filter($value, Request $request)
    {
        return preg_match('/^'.$value.'$/', $request->getServerIp());
    }
}
