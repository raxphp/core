<?php

namespace Rax\Router\Filter\Base;

use Rax\Http\Request;

/**
 * Secure route filter.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseSecureRouteFilter
{
    /**
     * Filters a route by checking if the request was made through HTTPS.
     *
     * @param bool    $value
     * @param Request $request
     *
     * @return bool
     */
    public function filter($value, Request $request)
    {
        return ($value === $request->isSecure());
    }
}
