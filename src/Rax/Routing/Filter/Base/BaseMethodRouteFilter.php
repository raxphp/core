<?php

namespace Rax\Routing\Filter\Base;

use Rax\Http\Request;

/**
 * Method route filter.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseMethodRouteFilter
{
    /**
     * Filters a route by checking the request's method value.
     *
     * @param string  $value
     * @param Request $request
     *
     * @return bool
     */
    public function filter($value, Request $request)
    {
        foreach (explode('|', $value) as $method) {
            if ($request->isMethod($method)) {
                return true;
            }
        }

        return false;
    }
}
